<?php

namespace App\Services\Publication;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;

class PublicationPlanResolver
{
    public function resolveFor(User $user): Plan
    {
        if ($user->hasRole('admin')) {
            return Plan::query()->where('slug', 'agencia-pro')->firstOrFail();
        }

        $subscription = Subscription::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('starts_at')
            ->latest('id')
            ->first();

        if ($subscription?->plan) {
            return $subscription->plan;
        }

        $defaultSlug = $user->hasRole('dealer') ? 'agencia' : 'basico';

        return Plan::query()->where('slug', $defaultSlug)->firstOrFail();
    }

    public function allowedPublicationTiersFor(User $user): array
    {
        $plan = $this->resolveFor($user);

        if ($user->hasRole('admin')) {
            return ['basic', 'estandar', 'premium', 'agencia', 'agencia-pro'];
        }

        return match ($plan->slug) {
            'basico' => ['basic'],
            'estandar' => ['basic', 'estandar'],
            'premium' => ['basic', 'estandar', 'premium'],
            'agencia' => ['basic', 'estandar', 'premium', 'agencia'],
            'agencia-pro' => ['basic', 'estandar', 'premium', 'agencia', 'agencia-pro'],
            default => ['basic'],
        };
    }
}
