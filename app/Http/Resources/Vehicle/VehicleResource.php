<?php

namespace App\Http\Resources\Vehicle;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $primaryMedia = $this->media->firstWhere('is_primary', true) ?? $this->media->first();

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'vin' => $this->vin,
            'plate_number' => $this->plate_number,
            'condition' => $this->condition,
            'year' => $this->year,
            'trim' => $this->trim,
            'body_type' => $this->body_type,
            'fuel_type' => $this->fuel_type,
            'transmission' => $this->transmission,
            'drivetrain' => $this->drivetrain,
            'mileage' => $this->mileage,
            'mileage_unit' => $this->mileage_unit,
            'engine' => $this->engine,
            'engine_size' => $this->engine_size,
            'exterior_color' => $this->exterior_color,
            'interior_color' => $this->interior_color,
            'doors' => $this->doors,
            'seats' => $this->seats,
            'price' => $this->price,
            'currency' => $this->currency,
            'original_price' => $this->original_price,
            'market_price' => $this->market_price,
            'price_badge' => $this->price_badge,
            'city' => $this->city,
            'state' => $this->state,
            'country_code' => $this->country_code,
            'postal_code' => $this->postal_code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'description' => $this->description,
            'features' => $this->features ?? [],
            'status' => $this->status,
            'publication_tier' => $this->publication_tier,
            'is_featured' => $this->is_featured,
            'is_verified_plate' => $this->is_verified_plate,
            'supports_360' => $this->supports_360,
            'has_video' => $this->has_video,
            'published_at' => $this->published_at,
            'expires_at' => $this->expires_at,
            'view_count' => $this->view_count,
            'lead_count' => $this->lead_count,
            'metadata' => $this->metadata ?? [],
            'owner' => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
                'account_type' => $this->owner?->account_type?->value,
                'agency_name' => $this->owner?->agency_name,
                'is_verified' => $this->owner?->is_verified,
            ],
            'make' => $this->make ? [
                'id' => $this->make->id,
                'name' => $this->make->name,
                'slug' => $this->make->slug,
            ] : null,
            'model' => $this->model ? [
                'id' => $this->model->id,
                'name' => $this->model->name,
                'slug' => $this->model->slug,
            ] : null,
            'lifestyle_categories' => $this->lifestyleCategories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'score' => $category->pivot?->score,
            ])->values(),
            'primary_image' => $primaryMedia ? new VehicleMediaResource($primaryMedia) : null,
            'media' => VehicleMediaResource::collection($this->media),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
