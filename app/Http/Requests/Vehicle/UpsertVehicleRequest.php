<?php

namespace App\Http\Requests\Vehicle;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpsertVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? ['required'] : ['sometimes'];

        return [
            'vehicle_make_id' => [...$required, 'integer', Rule::exists('vehicle_makes', 'id')],
            'vehicle_model_id' => [...$required, 'integer', Rule::exists('vehicle_models', 'id')],
            'title' => [...$required, 'string', 'max:255'],
            'vin' => ['nullable', 'string', 'max:32'],
            'plate_number' => ['nullable', 'string', 'max:30'],
            'condition' => [...$required, Rule::in(['new', 'used'])],
            'year' => [...$required, 'integer', 'min:1950', 'max:'.(now()->year + 1)],
            'trim' => ['nullable', 'string', 'max:100'],
            'body_type' => [...$required, 'string', 'max:60'],
            'fuel_type' => [...$required, 'string', 'max:60'],
            'transmission' => [...$required, 'string', 'max:60'],
            'drivetrain' => ['nullable', 'string', 'max:60'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'mileage_unit' => ['nullable', Rule::in(['km', 'mi'])],
            'engine' => ['nullable', 'string', 'max:100'],
            'engine_size' => ['nullable', 'numeric', 'min:0.0', 'max:10.0'],
            'exterior_color' => ['nullable', 'string', 'max:60'],
            'interior_color' => ['nullable', 'string', 'max:60'],
            'doors' => ['nullable', 'integer', 'min:1', 'max:8'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:20'],
            'price' => [...$required, 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'market_price' => ['nullable', 'numeric', 'min:0'],
            'price_badge' => ['nullable', 'string', 'max:60'],
            'city' => ['nullable', 'string', 'max:120'],
            'state' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'canton' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'location_label' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'description' => [...$required, 'string', 'min:20'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:100'],
            'lifestyle_category_ids' => ['nullable', 'array'],
            'lifestyle_category_ids.*' => ['integer', Rule::exists('lifestyle_categories', 'id')],
            'status' => ['nullable', Rule::in(['draft', 'published', 'paused', 'sold', 'archived'])],
            'publication_tier' => ['nullable', Rule::in(['basic', 'estándar', 'premium', 'agencia', 'agencia-pro'])],
            'is_verified_plate' => ['nullable', 'boolean'],
            'supports_360' => ['nullable', 'boolean'],
            'has_video' => ['nullable', 'boolean'],
            'expires_at' => ['nullable', 'date'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'optional_images' => ['nullable', 'array', 'max:8'],
            'optional_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'required_images' => ['nullable', 'array'],
            'required_images.*' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $makeId = $this->input('vehicle_make_id');
            $modelId = $this->input('vehicle_model_id');

            if (! $makeId || ! $modelId) {
                return;
            }

            $belongs = \App\Models\VehicleModel::query()
                ->whereKey($modelId)
                ->where('vehicle_make_id', $makeId)
                ->exists();

            if (! $belongs) {
                $validator->errors()->add('vehicle_model_id', 'El modelo seleccionado no pertenece a la marca indicada.');
            }
        });
    }
}
