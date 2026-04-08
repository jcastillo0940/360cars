<?php

namespace App\Services\Publication;

use App\Models\Plan;
use App\Models\User;
use App\Services\Billing\BillingService;

class PublicationPlanResolver
{
    public function __construct(
        private readonly BillingService $billingService,
    ) {
    }

    public function resolveFor(User $user): Plan
    {
        if ($user->hasRole('admin')) {
            return Plan::query()->where('slug', 'agencia-pro')->firstOrFail();
        }

        $basePlan = Plan::query()
            ->where('slug', 'basico')
            ->orWhere('is_active', true)
            ->orderBy('price')
            ->firstOrFail();

        $plan = clone $basePlan;
        $plan->slug = 'gratuito-ilimitado';
        $plan->name = 'Plan gratuito ilimitado';
        $plan->description = 'Temporalmente todas las publicaciones de usuarios registrados son gratuitas e ilimitadas.';
        $plan->price = 0;
        $plan->max_active_listings = null;
        $plan->photo_limit = null;
        $plan->allows_video = true;
        $plan->allows_360 = true;
        $plan->duration_days = null;
        $plan->priority_weight = max((int) $basePlan->priority_weight, 1);
        $plan->is_featured = false;

        return $plan;
    }

    public function allowedPublicationTiersFor(User $user): array
    {
        if ($user->hasRole('admin')) {
            return ['basic', 'est?ndar', 'premium', 'agencia', 'agencia-pro'];
        }

        return ['basic'];
    }
}