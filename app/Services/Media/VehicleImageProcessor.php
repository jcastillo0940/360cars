<?php

namespace App\Services\Media;

use App\Jobs\ProcessVehicleImageUpload;
use App\Models\Vehicle;
use App\Models\VehicleMedia;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class VehicleImageProcessor
{
    public function stageMany(Vehicle $vehicle, array $files): array
    {
        $ids = [];

        foreach ($files as $index => $file) {
            $media = $this->stage($vehicle, $file, ! $vehicle->media()->exists() && $index === 0);
            $ids[] = $media->id;
        }

        return $ids;
    }

    public function dispatchMany(array $mediaIds): void
    {
        if (app()->isLocal()) {
            foreach ($mediaIds as $mediaId) {
                $media = VehicleMedia::query()->find($mediaId);

                if (! $media || $media->processing_status === 'complete') {
                    continue;
                }

                try {
                    $this->processPending($media);
                } catch (\Throwable $exception) {
                    $this->markFailed($media, $exception->getMessage());
                    throw $exception;
                }
            }

            return;
        }

        foreach ($mediaIds as $mediaId) {
            ProcessVehicleImageUpload::dispatch($mediaId)
                ->onConnection(config('media.queue_connection'))
                ->onQueue(config('media.queue_name'));
        }
    }

    public function stage(Vehicle $vehicle, UploadedFile $file, bool $isPrimary = false): VehicleMedia
    {
        $stagingDisk = config('media.staging_disk', 'local');
        $storedPath = $file->store('vehicle-staging/'.$vehicle->id, $stagingDisk);

        if ($isPrimary) {
            $vehicle->media()->update(['is_primary' => false]);
        }

        $hasPrimary = $vehicle->media()->where('is_primary', true)->exists();

        return $vehicle->media()->create([
            'type' => 'image',
            'disk' => config('media.vehicle_disk', 'public'),
            'path' => '',
            'alt_text' => $vehicle->title,
            'mime_type' => $file->getMimeType() ?: 'image/jpeg',
            'extension' => strtolower($file->getClientOriginalExtension() ?: 'jpg'),
            'size_bytes' => $file->getSize(),
            'sort_order' => ((int) $vehicle->media()->max('sort_order')) + 1,
            'is_primary' => $isPrimary || ! $hasPrimary,
            'processing_status' => 'pending',
            'original_disk' => $stagingDisk,
            'original_path' => $storedPath,
            'conversions' => [],
        ]);
    }

    public function processPending(VehicleMedia $media): VehicleMedia
    {
        $originalDisk = Storage::disk($media->original_disk ?: config('media.staging_disk', 'local'));

        if (! $media->original_path || ! $originalDisk->exists($media->original_path)) {
            throw new RuntimeException('El archivo original temporal no existe para procesar la imagen.');
        }

        $binary = $originalDisk->get($media->original_path);

        if ($this->supportsGdProcessing()) {
            return $this->processWithGd($media, $originalDisk, $binary ?: '');
        }

        if ($this->supportsImagickProcessing()) {
            return $this->processWithImagick($media, $originalDisk, $binary ?: '');
        }

        return $this->storeOriginalWithoutTransforms($media, $originalDisk, $binary ?: '');
    }

    public function markFailed(VehicleMedia $media, string $message): void
    {
        $media->forceFill([
            'processing_status' => 'failed',
            'error_message' => Str::limit($message, 1000),
        ])->save();
    }

    public function makePrimary(Vehicle $vehicle, VehicleMedia $media): VehicleMedia
    {
        $vehicle->media()->update(['is_primary' => false]);
        $media->forceFill(['is_primary' => true])->save();

        return $media->refresh();
    }

    public function delete(VehicleMedia $media): void
    {
        if ($media->path !== '') {
            $disk = Storage::disk($media->disk);
            $disk->delete($media->path);

            foreach (($media->conversions ?? []) as $key => $path) {
                if (is_string($path) && ! str_ends_with($key, '_width') && ! str_ends_with($key, '_height')) {
                    $disk->delete($path);
                }
            }
        }

        if ($media->original_path && $media->original_disk) {
            Storage::disk($media->original_disk)->delete($media->original_path);
        }

        $wasPrimary = $media->is_primary;
        $vehicle = $media->vehicle;
        $media->delete();

        if ($wasPrimary) {
            $nextMedia = $vehicle->media()->orderBy('sort_order')->first();
            if ($nextMedia) {
                $nextMedia->forceFill(['is_primary' => true])->save();
            }
        }
    }

    public function deleteAllForVehicle(Vehicle $vehicle): void
    {
        foreach ($vehicle->media as $media) {
            $this->delete($media);
        }
    }

    private function processWithGd(VehicleMedia $media, $originalDisk, string $binary): VehicleMedia
    {
        $source = @\imagecreatefromstring($binary);

        if (! $source) {
            return $this->storeOriginalWithoutTransforms($media, $originalDisk, $binary);
        }

        $width = \imagesx($source);
        $height = \imagesy($source);
        $diskName = $media->disk ?: config('media.vehicle_disk', 'public');
        $disk = Storage::disk($diskName);
        $baseName = (string) Str::uuid();
        $directory = 'vehicles/'.$media->vehicle_id.'/images';
        $mainPath = $directory.'/'.$baseName.'.webp';
        $thumbPath = $directory.'/'.$baseName.'_thumb.webp';

        [$mainImage, $mainWidth, $mainHeight] = $this->resize($source, $width, $height, (int) config('media.main_max_width', 1920));
        [$thumbImage, $thumbWidth, $thumbHeight] = $this->resize($source, $width, $height, (int) config('media.thumb_max_width', 480));

        $mainBinary = $this->encodeWebp($mainImage);
        $thumbBinary = $this->encodeWebp($thumbImage);

        $disk->put($mainPath, $mainBinary, 'public');
        $disk->put($thumbPath, $thumbBinary, 'public');

        $media->forceFill([
            'path' => $mainPath,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size_bytes' => strlen($mainBinary),
            'width' => $mainWidth,
            'height' => $mainHeight,
            'processing_status' => 'complete',
            'error_message' => null,
            'processed_at' => now(),
            'conversions' => [
                'thumb' => $thumbPath,
                'thumb_width' => $thumbWidth,
                'thumb_height' => $thumbHeight,
            ],
        ])->save();

        $originalDisk->delete($media->original_path);
        $media->forceFill(['original_path' => null])->save();

        \imagedestroy($source);
        \imagedestroy($mainImage);
        \imagedestroy($thumbImage);

        return $media->refresh();
    }

    private function processWithImagick(VehicleMedia $media, $originalDisk, string $binary): VehicleMedia
    {
        $image = new \Imagick();
        $image->readImageBlob($binary);

        if ($image->getNumberImages() > 1) {
            $image = $image->coalesceImages();
        }

        $image->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $diskName = $media->disk ?: config('media.vehicle_disk', 'public');
        $disk = Storage::disk($diskName);
        $baseName = (string) Str::uuid();
        $directory = 'vehicles/'.$media->vehicle_id.'/images';
        $mainPath = $directory.'/'.$baseName.'.webp';
        $thumbPath = $directory.'/'.$baseName.'_thumb.webp';

        $main = clone $image;
        $this->resizeImagick($main, (int) config('media.main_max_width', 1920));
        $main->setImageFormat('webp');
        $main->setImageCompressionQuality((int) config('media.webp_quality', 82));

        $thumb = clone $image;
        $this->resizeImagick($thumb, (int) config('media.thumb_max_width', 480));
        $thumb->setImageFormat('webp');
        $thumb->setImageCompressionQuality((int) config('media.webp_quality', 82));

        $mainBinary = $main->getImagesBlob();
        $thumbBinary = $thumb->getImagesBlob();

        $disk->put($mainPath, $mainBinary, 'public');
        $disk->put($thumbPath, $thumbBinary, 'public');

        $media->forceFill([
            'path' => $mainPath,
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size_bytes' => strlen($mainBinary),
            'width' => $main->getImageWidth(),
            'height' => $main->getImageHeight(),
            'processing_status' => 'complete',
            'error_message' => null,
            'processed_at' => now(),
            'conversions' => [
                'thumb' => $thumbPath,
                'thumb_width' => $thumb->getImageWidth(),
                'thumb_height' => $thumb->getImageHeight(),
            ],
        ])->save();

        $originalDisk->delete($media->original_path);
        $media->forceFill(['original_path' => null])->save();

        $image->clear();
        $main->clear();
        $thumb->clear();

        return $media->refresh();
    }

    private function storeOriginalWithoutTransforms(VehicleMedia $media, $originalDisk, string $binary): VehicleMedia
    {
        if ($binary === '') {
            throw new RuntimeException('No fue posible leer la imagen subida.');
        }

        $diskName = $media->disk ?: config('media.vehicle_disk', 'public');
        $disk = Storage::disk($diskName);
        $baseName = (string) Str::uuid();
        $extension = strtolower($media->extension ?: 'jpg');
        $directory = 'vehicles/'.$media->vehicle_id.'/images';
        $mainPath = $directory.'/'.$baseName.'.'.$extension;

        $dimensions = $this->detectDimensions($binary);

        $disk->put($mainPath, $binary, 'public');

        $media->forceFill([
            'path' => $mainPath,
            'mime_type' => $media->mime_type ?: $this->detectMimeType($binary, $extension),
            'extension' => $extension,
            'size_bytes' => strlen($binary),
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'processing_status' => 'complete',
            'error_message' => null,
            'processed_at' => now(),
            'conversions' => [],
        ])->save();

        $originalDisk->delete($media->original_path);
        $media->forceFill(['original_path' => null])->save();

        return $media->refresh();
    }

    private function supportsGdProcessing(): bool
    {
        return function_exists('imagecreatefromstring')
            && function_exists('imagecreatetruecolor')
            && function_exists('imagecopyresampled')
            && function_exists('imagewebp');
    }

    private function supportsImagickProcessing(): bool
    {
        return class_exists(\Imagick::class);
    }

    private function detectDimensions(string $binary): array
    {
        if (! function_exists('getimagesizefromstring')) {
            return ['width' => null, 'height' => null];
        }

        $dimensions = @getimagesizefromstring($binary);

        return [
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
        ];
    }

    private function detectMimeType(string $binary, string $fallbackExtension): string
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo !== false) {
                $mime = finfo_buffer($finfo, $binary) ?: null;
                finfo_close($finfo);
                if (is_string($mime) && $mime !== '') {
                    return $mime;
                }
            }
        }

        return match ($fallbackExtension) {
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            'avif' => 'image/avif',
            default => 'image/jpeg',
        };
    }

    private function resizeImagick(\Imagick $image, int $maxWidth): void
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();

        if ($width <= $maxWidth || $width === 0 || $height === 0) {
            return;
        }

        $targetHeight = (int) round(($height / $width) * $maxWidth);
        $image->thumbnailImage($maxWidth, $targetHeight, true);
    }

    private function resize($source, int $width, int $height, int $maxWidth): array
    {
        if ($width <= $maxWidth) {
            $canvas = \imagecreatetruecolor($width, $height);
            \imagealphablending($canvas, false);
            \imagesavealpha($canvas, true);
            $transparent = \imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            \imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
            \imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $width, $height);

            return [$canvas, $width, $height];
        }

        $targetWidth = $maxWidth;
        $targetHeight = (int) round(($height / $width) * $targetWidth);
        $canvas = \imagecreatetruecolor($targetWidth, $targetHeight);
        \imagealphablending($canvas, false);
        \imagesavealpha($canvas, true);
        $transparent = \imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        \imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        \imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return [$canvas, $targetWidth, $targetHeight];
    }

    private function encodeWebp($image): string
    {
        ob_start();
        \imagewebp($image, null, (int) config('media.webp_quality', 82));
        return (string) ob_get_clean();
    }
}
