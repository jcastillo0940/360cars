<?php

namespace App\Http\Resources\Billing;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'external_reference' => $this->external_reference,
            'payload' => $this->payload ?? [],
            'paid_at' => $this->paid_at,
            'plan' => $this->plan ? new PlanResource($this->plan) : null,
            'created_at' => $this->created_at,
        ];
    }
}
