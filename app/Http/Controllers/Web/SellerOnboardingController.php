<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\SellerOnboardingStoreRequest;
use App\Models\Province;
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
        $googleMapsKey = (string) $this->valuationSettings->get('integrations.google_maps.key', config('services.google_maps.key'));

        return view('seller.onboarding', [
            'makes' => VehicleMake::query()->active()->with(['models' => fn ($query) => $query->active()->orderBy('name')])->orderBy('name')->get(),
            'locationTree' => Province::query()
                ->with([
                    'cantons' => fn ($query) => $query->orderBy('name'),
                    'cantons.districts' => fn ($query) => $query->orderBy('name'),
                ])
                ->orderBy('name')
                ->get()
                ->map(fn (Province $province) => [
                    'name' => $province->name,
                    'cantons' => $province->cantons->map(fn ($canton) => [
                        'name' => $canton->name,
                        'districts' => $canton->districts->pluck('name')->values()->all(),
                    ])->values()->all(),
                ])
                ->values()
                ->all(),
            'featureOptions' => VehicleFeatureOption::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'countryOptions' => User::countryOptions(),
            'vehicleConfig' => config('vehicle'),
            'googleMapsEnabled' => filled($googleMapsKey),
            'years' => range((int) date('Y') + 1, 1950),
            'googleMapsKey' => $googleMapsKey,
            'exchangeQuote' => $this->exchangeRateService->latest(),
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'currentUser' => $request->user(),
            'prefill' => [
                'vehicle_make_id' => $request->query('vehicle_make_id'),
                'vehicle_model_id' => $request->query('vehicle_model_id'),
                'year' => $request->query('year'),
                'condition' => $request->query('condition'),
                'body_type' => $request->query('body_type'),
                'fuel_type' => $request->query('fuel_type'),
                'transmission' => $request->query('transmission'),
                'drivetrain' => $request->query('drivetrain'),
                'mileage' => $request->query('mileage'),
                'price' => $request->query('price'),
                'city' => $request->query('city'),
                'province' => $request->query('province'),
                'canton' => $request->query('canton'),
                'district' => $request->query('district'),
            ],
        ]);
    }

    public function store(SellerOnboardingStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $queuedMediaIds = [];
        $wasAuthenticated = $request->user() !== null;

        DB::transaction(function () use ($data, $request, &$queuedMediaIds): void {
            $user = $request->user();

            if (! $user) {
                $email = $data['contact_email'] ?? $this->syntheticEmail($data['contact_phone'] ?? null);

                $user = User::create([
                    'name' => $data['seller_name'],
                    'email' => strtolower($email),
                    'password' => $data['password'],
                    'account_type' => 'seller',
                    'phone' => $data['contact_phone'] ?? null,
                    'whatsapp_phone' => $data['contact_phone'] ?? null,
                    'country_code' => strtoupper((string) ($data['contact_country_code'] ?? 'CR')),
                    'last_seen_at' => now(),
                ]);

                Auth::login($user);
                $request->session()->regenerate();
            } else {
                $updates = [
                    'last_seen_at' => now(),
                ];

                if (! empty($data['contact_phone']) && ! $user->phone) {
                    $updates['phone'] = $data['contact_phone'];
                }

                if (! empty($data['contact_phone']) && ! $user->whatsapp_phone) {
                    $updates['whatsapp_phone'] = $data['contact_phone'];
                }

                if (! empty($data['contact_country_code'])) {
                    $updates['country_code'] = strtoupper((string) $data['contact_country_code']);
                }

                if (! empty($data['contact_email']) && ! $user->email) {
                    $updates['email'] = strtolower($data['contact_email']);
                }

                if (! empty($data['seller_name']) && ! $user->name) {
                    $updates['name'] = $data['seller_name'];
                }

                $user->fill($updates)->save();
            }

            $this->limitGuard->ensureCanUseTier($user, 'basic');
            $this->limitGuard->ensureCanPublish($user);

            $make = VehicleMake::findOrFail($data['vehicle_make_id']);
            $model = $make->models()->findOrFail($data['vehicle_model_id']);
            $title = trim($make->name.' '.$model->name.' '.$data['year']);

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
                'body_type' => $data['body_type'],
                'fuel_type' => $data['fuel_type'],
                'transmission' => $data['transmission'],
                'drivetrain' => $data['drivetrain'] ?? null,
                'mileage' => $data['mileage'] ?? null,
                'mileage_unit' => 'km',
                'engine' => $data['engine'] ?? null,
                'exterior_color' => $data['exterior_color'] ?? null,
                'interior_color' => $data['interior_color'] ?? null,
                'doors' => $data['doors'] ?? null,
                'price' => $data['price'],
                'currency' => 'CRC',
                'city' => $data['district'] ?? $data['city'],
                'state' => $data['province'] ?? $data['state'],
                'province' => $data['province'] ?? $data['state'],
                'canton' => $data['canton'] ?? null,
                'district' => $data['district'] ?? $data['city'],
                'country_code' => 'CR',
                'latitude' => $data['latitude'] ?? null,
                'longitude' => $data['longitude'] ?? null,
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
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return redirect()->route('seller.dashboard')
            ->with('status', $wasAuthenticated
                ? 'Tu auto fue publicado con tu cuenta actual y ya quedo listo en tu panel seller.'
                : 'Tu auto fue registrado y tu cuenta seller quedo lista.')
            ->with('onboarding_finished', true);
    }

    private function syntheticEmail(?string $phone): string
    {
        $base = preg_replace('/\D+/', '', (string) $phone) ?: Str::random(10);

        return $base.'@phone.movikaa.local';
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

