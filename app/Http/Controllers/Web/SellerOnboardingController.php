<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerOnboardingStoreRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleMake;
use App\Services\Currency\ExchangeRateService;
use App\Services\Media\VehicleImageProcessor;
use App\Services\Publication\PublicationLifecycleService;
use App\Services\Publication\PublicationLimitGuard;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerOnboardingController extends Controller
{
    public function __construct(
        private readonly VehicleImageProcessor $imageProcessor,
        private readonly PublicationLimitGuard $limitGuard,
        private readonly PublicationLifecycleService $lifecycleService,
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $valuationSettings,
    ) {
    }

    public function create(Request $request)
    {
        return view('seller.onboarding', [
            'makes' => VehicleMake::query()->with('models')->orderBy('name')->get(),
            'featureOptions' => VehicleFeatureOption::query()
                ->where('is_active', true)
                ->orderBy('category')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->groupBy('category'),
            'vehicleConfig' => config('vehicle'),
            'years' => range((int) date('Y') + 1, 1950),
            'googleMapsKey' => config('services.google_maps.key'),
            'exchangeQuote' => $this->exchangeRateService->latest(),
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'prefill' => [
                'vehicle_make_id' => $request->query('vehicle_make_id'),
                'vehicle_model_id' => $request->query('vehicle_model_id'),
                'year' => $request->query('year'),
                'trim' => $request->query('trim'),
                'condition' => $request->query('condition'),
                'body_type' => $request->query('body_type'),
                'fuel_type' => $request->query('fuel_type'),
                'transmission' => $request->query('transmission'),
                'drivetrain' => $request->query('drivetrain'),
                'mileage' => $request->query('mileage'),
                'engine_size' => $request->query('engine_size'),
                'price' => $request->query('price'),
                'city' => $request->query('city'),
            ],
        ]);
    }

    public function store(SellerOnboardingStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $queuedMediaIds = [];

        DB::transaction(function () use ($data, $request, &$queuedMediaIds): void {
            $email = $data['contact_email'] ?? $this->syntheticEmail($data['contact_phone']);

            $user = User::create([
                'name' => $data['seller_name'],
                'email' => strtolower($email),
                'password' => $data['password'],
                'account_type' => 'seller',
                'phone' => $data['contact_phone'] ?? null,
                'whatsapp_phone' => $data['contact_phone'] ?? null,
                'country_code' => 'CR',
                'last_seen_at' => now(),
            ]);

            $this->limitGuard->ensureCanUseTier($user, 'basic');
            $this->limitGuard->ensureCanPublish($user);

            $make = VehicleMake::findOrFail($data['vehicle_make_id']);
            $model = $make->models()->findOrFail($data['vehicle_model_id']);
            $title = trim($make->name.' '.$model->name.' '.$data['year'].' '.($data['trim'] ?? ''));

            $vehicle = Vehicle::create([
                'user_id' => $user->id,
                'vehicle_make_id' => $data['vehicle_make_id'],
                'vehicle_model_id' => $data['vehicle_model_id'],
                'title' => $title,
                'slug' => $this->uniqueSlug($title, (int) $data['year']),
                'vin' => $data['vin'] ?? null,
                'plate_number' => $data['plate_number'] ?? null,
                'condition' => $data['condition'],
                'year' => $data['year'],
                'trim' => $data['trim'] ?? null,
                'body_type' => $data['body_type'],
                'fuel_type' => $data['fuel_type'],
                'transmission' => $data['transmission'],
                'drivetrain' => $data['drivetrain'] ?? null,
                'mileage' => $data['mileage'] ?? null,
                'mileage_unit' => 'km',
                'engine' => $data['engine'] ?? null,
                'engine_size' => $data['engine_size'] ?? null,
                'exterior_color' => $data['exterior_color'] ?? null,
                'interior_color' => $data['interior_color'] ?? null,
                'doors' => $data['doors'] ?? null,
                'seats' => $data['seats'] ?? null,
                'price' => $data['price'],
                'currency' => 'CRC',
                'city' => $data['city'],
                'state' => $data['state'],
                'country_code' => 'CR',
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'description' => $data['description'],
                'features' => $this->features($data['features'] ?? [], $data['features_list'] ?? null),
                'status' => 'published',
                'publication_tier' => 'basic',
                'published_at' => now(),
                'metadata' => [
                    'seller_onboarding' => true,
                    'location_label' => $data['location_label'],
                ],
            ]);

            $vehicle = $this->lifecycleService->applyPlanBenefits($user, $vehicle, 'published');

            $photoMap = [
                'photo_front' => 'Fotografia frontal',
                'photo_rear' => 'Fotografia trasera',
                'photo_left' => 'Lateral izquierda',
                'photo_right' => 'Lateral derecha',
                'photo_driver_interior' => 'Interior del conductor',
                'photo_passenger_interior' => 'Interior del copiloto',
                'photo_dashboard' => 'Tablero',
                'photo_back_seats' => 'Asientos traseros',
                'photo_engine' => 'Motor',
                'photo_trunk' => 'Baul',
                'photo_wheels' => 'Aros y llantas',
                'photo_extra_1' => 'Detalle adicional 1',
                'photo_extra_2' => 'Detalle adicional 2',
            ];

            $isPrimary = true;
            foreach ($photoMap as $field => $label) {
                if (! $request->hasFile($field)) {
                    continue;
                }

                $media = $this->imageProcessor->stage($vehicle, $request->file($field), $isPrimary);
                $media->forceFill(['alt_text' => $label])->save();
                $queuedMediaIds[] = $media->id;
                $isPrimary = false;
            }

            Auth::login($user);
            $request->session()->regenerate();
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return redirect()->route('seller.dashboard')->with('status', 'Tu auto fue registrado y tu cuenta seller quedo lista.');
    }

    private function syntheticEmail(?string $phone): string
    {
        $base = preg_replace('/\D+/', '', (string) $phone) ?: Str::random(10);

        return $base.'@phone.360cars.local';
    }

    private function features(array $features = [], ?string $legacyFeatures = null): array
    {
        $normalized = array_values(array_filter(array_map('strval', $features)));

        if ($normalized !== []) {
            return array_values(array_unique($normalized));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $legacyFeatures))));
    }

    private function uniqueSlug(string $title, int $year): string
    {
        $base = Str::slug(trim($title).' '.$year) ?: 'vehiculo';
        $slug = $base;
        $counter = 2;

        while (Vehicle::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
