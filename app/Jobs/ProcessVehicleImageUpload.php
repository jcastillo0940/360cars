<?php

namespace App\Jobs;

use App\Models\VehicleMedia;
use App\Services\Media\VehicleImageProcessor;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessVehicleImageUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly int $mediaId)
    {
    }

    public function handle(VehicleImageProcessor $processor): void
    {
        $media = VehicleMedia::query()->find($this->mediaId);

        if (! $media || $media->processing_status === 'complete') {
            return;
        }

        try {
            $processor->processPending($media);
        } catch (\Throwable $exception) {
            $processor->markFailed($media, $exception->getMessage());
            throw $exception;
        }
    }
}
