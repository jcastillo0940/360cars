<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'audience' => $this->audience,
            'price' => $this->price,
            'currency' => $this->currency,
            'billing_interval' => $this->billing_interval,
            'duration_days' => $this->duration_days,
            'max_active_listings' => $this->max_active_listings,
            'photo_limit' => $this->photo_limit,
            'allows_video' => $this->allows_video,
            'allows_360' => $this->allows_360,
            'supports_credit_leads' => $this->supports_credit_leads,
            'supports_trade_in' => $this->supports_trade_in,
            'priority_weight' => $this->priority_weight,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'metadata' => $this->metadata ?? [],
        ];
    }
}
