<?php

namespace App\Http\Requests\Seller;

use App\Models\Canton;
use App\Models\District;
use App\Models\Province;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleModel;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class SellerOnboardingStoreRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $province = trim((string) $this->input('province'));
        $canton = trim((string) $this->input('canton'));
        $district = trim((string) $this->input('district'));
        $locationLabel = trim((string) $this->input('location_label'));

        $merge = [];

        if ($district !== '' && ! $this->filled('city')) {
            $merge['city'] = $district;
        }

        if ($province !== '' && ! $this->filled('state')) {
            $merge['state'] = $province;
        }

        if ($locationLabel === '' && ($district !== '' || $canton !== '' || $province !== '')) {
            $merge['location_label'] = collect([$district, $canton, $province, 'Costa Rica'])
                ->filter()
                ->implode(', ');
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $googleMapsKey = (string) app(ValuationSettingsService::class)->get('integrations.google_maps.key', config('services.google_maps.key'));
        $hasGoogleMaps = filled($googleMapsKey);
        $currentUser = $this->user();

        return [
            'vehicle_make_id' => ['required', 'integer', 'exists:vehicle_makes,id'],
            'vehicle_model_id' => ['required', 'integer', 'exists:vehicle_models,id'],
            'year' => ['required', 'integer', 'min:1950', 'max:2100'],
            'trim' => ['nullable', 'string', 'max:100'],
            'condition' => ['required', 'in:new,used'],
            'body_type' => ['required', 'string', 'in:'.implode(',', config('vehicle.body_types', []))],
            'fuel_type' => ['required', 'string', 'in:'.implode(',', config('vehicle.fuel_types', []))],
            'transmission' => ['required', 'string', 'in:'.implode(',', config('vehicle.transmissions', []))],
            'drivetrain' => ['nullable', 'string', 'in:'.implode(',', config('vehicle.drivetrains', []))],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'engine' => ['nullable', 'string', 'max:100'],
            'engine_size' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'exterior_color' => ['nullable', 'string', 'max:60'],
            'interior_color' => ['nullable', 'string', 'max:60'],
            'doors' => ['nullable', 'integer', 'min:1', 'max:8'],
            'seats' => ['nullable', 'integer', 'min:1', 'max:20'],
            'price' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'in:CRC'],
            'province' => ['nullable', 'string', 'max:120'],
            'canton' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],
            'state' => ['required', 'string', 'max:120'],
            'country_code' => ['required', 'in:CR'],
            'latitude' => [$hasGoogleMaps ? 'nullable' : 'nullable', 'numeric', 'between:8,12'],
            'longitude' => [$hasGoogleMaps ? 'nullable' : 'nullable', 'numeric', 'between:-86,-82'],
            'location_label' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:100'],
            'features_list' => ['nullable', 'string', 'max:2000'],
            'vin' => ['nullable', 'string', 'max:32'],
            'plate_number' => ['nullable', 'string', 'max:30'],

            'photo_front' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_rear' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_left' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_right' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_driver_interior' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_passenger_interior' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_dashboard' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_back_seats' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_engine' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_trunk' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_wheels' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_extra_1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'photo_extra_2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],

            'seller_name' => [$currentUser ? 'nullable' : 'required', 'string', 'max:255'],
            'contact_email' => [
                'nullable',
                'email',
                'max:191',
                Rule::unique('users', 'email')->ignore($currentUser?->id),
            ],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'password' => [$currentUser ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'accept_terms' => [$currentUser ? 'nullable' : 'accepted'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $currentUser = $this->user();

            if (! $currentUser && ! $this->filled('contact_email') && ! $this->filled('contact_phone')) {
                $validator->errors()->add('contact_email', 'Debes indicar un correo o un telefono para crear la cuenta.');
            }

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

            $googleMapsKey = (string) app(ValuationSettingsService::class)->get('integrations.google_maps.key', config('services.google_maps.key'));
            $hasGoogleMaps = filled($googleMapsKey);
            $provinceName = trim((string) $this->input('province'));
            $cantonName = trim((string) $this->input('canton'));
            $districtName = trim((string) $this->input('district'));

            if ($provinceName !== '' || $cantonName !== '' || $districtName !== '') {
                $province = Province::query()->where('name', $provinceName)->first();

                if (! $province) {
                    $validator->errors()->add('province', 'Debes seleccionar una provincia valida de Costa Rica.');
                }

                $canton = null;
                if ($province) {
                    $canton = Canton::query()
                        ->where('province_id', $province->id)
                        ->where('name', $cantonName)
                        ->first();
                }

                if (! $canton) {
                    $validator->errors()->add('canton', 'Debes seleccionar un canton valido para la provincia indicada.');
                }

                if ($canton) {
                    $district = District::query()
                        ->where('canton_id', $canton->id)
                        ->where('name', $districtName)
                        ->first();

                    if (! $district) {
                        $validator->errors()->add('district', 'Debes seleccionar un distrito valido para el canton indicado.');
                    }
                }
            } elseif (! $this->filled('city') || ! $this->filled('state')) {
                $validator->errors()->add('province', 'Debes indicar la provincia, el canton y el distrito del auto.');
            }

            if (! $hasGoogleMaps) {
                if (! $this->filled('city')) {
                    $validator->errors()->add('city', 'Debes indicar el distrito manualmente si Google Maps no esta configurado.');
                }

                if (! $this->filled('state')) {
                    $validator->errors()->add('state', 'Debes indicar la provincia manualmente si Google Maps no esta configurado.');
                }
            }

            if ($this->filled('features')) {
                $validFeatureSlugs = VehicleFeatureOption::query()
                    ->where('is_active', true)
                    ->pluck('slug')
                    ->all();

                foreach ((array) $this->input('features', []) as $feature) {
                    if (! in_array($feature, $validFeatureSlugs, true)) {
                        $validator->errors()->add('features', 'Uno de los extras seleccionados ya no esta disponible.');
                        break;
                    }
                }
            }
        });
    }
}
