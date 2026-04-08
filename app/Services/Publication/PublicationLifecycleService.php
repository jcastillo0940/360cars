<?php

namespace App\Services\Publication;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

class PublicationLifecycleService
{
    public function __construct(private readonly PublicationLimitGuard $limitGuard)
    {
    }

    public function applyPlanBenefits(User $user, Vehicle $vehicle, ?string $requestedStatus = null): Vehicle
    {
        $plan = $this->limitGuard->planFor($user);
        $status = $requestedStatus ?? $vehicle->status;

        if ($vehicle->supports_360 && ! $plan->allows_360) {
            throw ValidationException::withMessages([
                'supports_360' => ['Tu plan actual no permite fotos 360.'],
            ]);
        }

        if ($vehicle->has_video && ! $plan->allows_video) {
            throw ValidationException::withMessages([
                'has_video' => ['Tu plan actual no permite video walkaround.'],
            ]);
        }

        $capabilities = $this->limitGuard->capabilities($user);
        $allowedTiers = $capabilities['allowed_tiers'] ?? [];
        $shouldFeature = in_array($vehicle->publication_tier, ['premium', 'agencia', 'agencia-pro'], true)
            && in_array($vehicle->publication_tier, $allowedTiers, true);

        $vehicle->forceFill([
            'is_featured' => $status === 'published' ? $shouldFeature : false,
            'expires_at' => $status === 'published' && $plan->duration_days
                ? now()->addDays($plan->duration_days)
                : $vehicle->expires_at,
            'metadata' => array_merge($vehicle->metadata ?? [], [
                'plan_slug' => $plan->slug,
                'plan_name' => $plan->name,
                'plan_priority_weight' => $plan->priority_weight,
                'plan_price' => (float) $plan->price,
                'plan_is_paid' => (float) $plan->price > 0,
                'visibility_bucket' => $this->visibilityBucket($plan->slug),
            ]),
        ])->save();

        return $vehicle->refresh();
    }

    public function canRefreshPublication(User $user, Vehicle $vehicle): bool
    {
        $plan = $this->limitGuard->planFor($user);

        return $vehicle->user_id === $user->id
            && $vehicle->publication_tier === 'basic'
            && $plan->slug === 'basico';
    }

    public function refreshBasicPublication(User $user, Vehicle $vehicle): Vehicle
    {
        if (! $this->canRefreshPublication($user, $vehicle)) {
            throw ValidationException::withMessages([
                'vehicle' => ['Solo las publicaciones basicas pueden renovarse gratis desde este flujo.'],
            ]);
        }

        $this->limitGuard->ensureCanPublish($user, $vehicle);

        $metadata = $vehicle->metadata ?? [];
        $metadata['basic_refresh_count'] = (int) ($metadata['basic_refresh_count'] ?? 0) + 1;
        $metadata['last_basic_refresh_at'] = now()->toISOString();

        $vehicle->forceFill([
            'status' => 'published',
            'published_at' => now(),
            'metadata' => $metadata,
        ])->save();

        return $this->applyPlanBenefits($user, $vehicle, 'published');
    }

    private function visibilityBucket(string $planSlug): string
    {
        return match ($planSlug) {
            'basico' => 'standard',
            'est?ndar' => 'priority',
            'premium' => 'featured',
            'agencia' => 'dealer-boost',
            'agencia-pro' => 'maximum',
            default => 'standard',
        };
    }
}
