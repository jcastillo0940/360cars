<?php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleMediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $pathUrl = $this->path !== '' ? Storage::disk($this->disk)->url($this->path) : null;
        $conversions = collect($this->conversions ?? [])->mapWithKeys(function ($path, $key) {
            if (! is_string($path)) {
                return [$key => $path];
            }

            return [$key => [
                'path' => $path,
                'url' => Storage::disk($this->disk)->url($path),
            ]];
        });

        return [
            'id' => $this->id,
            'type' => $this->type,
            'disk' => $this->disk,
            'path' => $this->path !== '' ? $this->path : null,
            'url' => $pathUrl,
            'alt_text' => $this->alt_text,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'size_bytes' => $this->size_bytes,
            'width' => $this->width,
            'height' => $this->height,
            'sort_order' => $this->sort_order,
            'is_primary' => $this->is_primary,
            'processing_status' => $this->processing_status,
            'error_message' => $this->error_message,
            'processed_at' => $this->processed_at,
            'is_ready' => $this->processing_status === 'complete',
            'conversions' => $conversions,
            'created_at' => $this->created_at,
        ];
    }
}
