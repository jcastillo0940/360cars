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
        $source = @imagecreatefromstring($binary ?: '');

        if (! $source) {
            throw new RuntimeException('No fue posible procesar la imagen subida.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
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

        imagedestroy($source);
        imagedestroy($mainImage);
        imagedestroy($thumbImage);

        return $media->refresh();
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

    private function resize($source, int $width, int $height, int $maxWidth): array
    {
        if ($width <= $maxWidth) {
            $canvas = imagecreatetruecolor($width, $height);
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
            $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $transparent);
            imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, $width, $height);

            return [$canvas, $width, $height];
        }

        $targetWidth = $maxWidth;
        $targetHeight = (int) round(($height / $width) * $targetWidth);
        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        return [$canvas, $targetWidth, $targetHeight];
    }

    private function encodeWebp($image): string
    {
        ob_start();
        imagewebp($image, null, (int) config('media.webp_quality', 82));
        return (string) ob_get_clean();
    }
}
