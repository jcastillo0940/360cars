<?php

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'account_type' => $this->account_type->value,
            'phone' => $this->phone,
            'whatsapp_phone' => $this->whatsapp_phone,
            'agency_name' => $this->agency_name,
            'company_name' => $this->company_name,
            'country_code' => $this->country_code,
            'is_verified' => $this->is_verified,
            'verified_at' => $this->verified_at,
            'rating_average' => $this->rating_average,
            'rating_count' => $this->rating_count,
            'last_seen_at' => $this->last_seen_at,
            'created_at' => $this->created_at,
        ];
    }
}
