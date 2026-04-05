<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'auto_renews' => $this->auto_renews,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'external_reference' => $this->external_reference,
            'metadata' => $this->metadata ?? [],
            'plan' => $this->plan ? new PlanResource($this->plan) : null,
            'created_at' => $this->created_at,
        ];
    }
}
