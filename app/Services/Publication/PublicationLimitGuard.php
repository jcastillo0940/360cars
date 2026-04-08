<?php

namespace App\Services\Publication;

use App\Models\Plan;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Validation\ValidationException;

class PublicationLimitGuard
{
    public function __construct(private readonly PublicationPlanResolver $resolver)
    {
    }

    public function capabilities(User $user): array
    {
        $plan = $this->resolver->resolveFor($user);
        $activeListings = Vehicle::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['published', 'paused'])
            ->count();

        return [
            'plan' => $plan,
            'allowed_tiers' => $this->resolver->allowedPublicationTiersFor($user),
            'active_listings' => $activeListings,
            'remaining_active_listings' => $plan->max_active_listings === null
                ? null
                : max($plan->max_active_listings - $activeListings, 0),
            'max_active_listings' => $plan->max_active_listings,
            'max_photos' => $plan->photo_limit,
            'can_upload_video' => (bool) $plan->allows_video,
            'can_use_360' => (bool) $plan->allows_360,
        ];
    }

    public function ensureCanUseTier(User $user, string $publicationTier): void
    {
        if (! in_array($publicationTier, $this->resolver->allowedPublicationTiersFor($user), true)) {
            throw ValidationException::withMessages([
                'publication_tier' => ['Tu plan actual no permite este nivel de publicación.'],
            ]);
        }
    }

    public function ensureCanPublish(User $user, ?Vehicle $vehicle = null): void
    {
        $plan = $this->resolver->resolveFor($user);

        if ($plan->max_active_listings === null) {
            return;
        }

        $activeListings = Vehicle::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['published', 'paused'])
            ->when($vehicle, fn ($query) => $query->where('id', '!=', $vehicle->id))
            ->count();

        if ($activeListings >= $plan->max_active_listings) {
            throw ValidationException::withMessages([
                'status' => ['Ya alcanzaste el límite de publicaciones activas de tu plan.'],
            ]);
        }
    }

    public function ensureCanUploadImages(User $user, int $totalImages): void
    {
        $plan = $this->resolver->resolveFor($user);

        if ($plan->photo_limit !== null && $totalImages > $plan->photo_limit) {
            throw ValidationException::withMessages([
                'images' => ["Tu plan permite un máximo de {$plan->photo_limit} fotos por publicación."],
            ]);
        }
    }

    public function planFor(User $user): Plan
    {
        return $this->resolver->resolveFor($user);
    }
}
