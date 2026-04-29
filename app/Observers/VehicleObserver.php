<?php

namespace App\Observers;

use App\Jobs\SubmitIndexNowUrls;
use App\Models\Vehicle;

class VehicleObserver
{
    public function saved(Vehicle $vehicle): void
    {
        if (! $this->isIndexable($vehicle)) {
            return;
        }

        SubmitIndexNowUrls::dispatchAfterResponse([
            route('catalog.show', $vehicle->slug),
        ]);
    }

    public function deleted(Vehicle $vehicle): void
    {
        if (! $vehicle->slug) {
            return;
        }

        SubmitIndexNowUrls::dispatchAfterResponse([
            route('catalog.show', $vehicle->slug),
        ]);
    }

    private function isIndexable(Vehicle $vehicle): bool
    {
        return $vehicle->status === 'published'
            && (! $vehicle->expires_at || $vehicle->expires_at->isFuture())
            && ! $vehicle->trashed();
    }
}
