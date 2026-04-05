<?php

namespace App\Http\Requests\Valuation;

use App\Models\VehicleModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVehicleValuationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'vehicle_make_id' => ['required', 'integer', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => ['required', 'integer', 'exists:vehicle_models,id'],
            'year' => ['required', 'integer', 'min:1950', 'max:2100'],
            'condition' => ['required', 'in:new,used'],
            'body_type' => ['required', 'string', 'in:'.implode(',', config('vehicle.body_types', []))],
            'fuel_type' => ['required', 'string', 'in:'.implode(',', config('vehicle.fuel_types', []))],
            'transmission' => ['required', 'string', 'in:'.implode(',', config('vehicle.transmissions', []))],
            'drivetrain' => ['nullable', 'string', 'in:'.implode(',', config('vehicle.drivetrains', []))],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'engine_size' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'city' => ['required', 'string', 'max:120'],
            'price_reference' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $makeId = $this->input('vehicle_make_id');
            $modelId = $this->input('vehicle_model_id');

            if ($makeId && $modelId) {
                $belongs = VehicleModel::query()
                    ->whereKey($modelId)
                    ->where('vehicle_make_id', $makeId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add('vehicle_model_id', 'El modelo seleccionado no pertenece a la marca indicada.');
                }
            }
        });
    }
}
