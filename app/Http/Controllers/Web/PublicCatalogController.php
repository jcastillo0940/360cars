<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Services\Currency\ExchangeRateService;
use App\Services\Valuation\ValuationSettingsService;
use App\Support\VehiclePricePresenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class PublicCatalogController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $valuationSettings,
    ) {
    }

    public function index(Request $request)
    {
        $filters = [
            'make' => $request->string('make')->toString(),
            'model' => $request->string('model')->toString(),
            'province' => $request->string('province')->toString(),
            'features' => collect($request->input('features', []))->filter()->map(fn ($feature) => (string) $feature)->values()->all(),
            'min_price' => $request->integer('min_price') ?: null,
            'max_price' => $request->integer('max_price') ?: null,
            'min_year' => $request->integer('min_year') ?: null,
            'max_year' => $request->integer('max_year') ?: null,
            'offers' => $request->boolean('offers'),
            'featured' => $request->boolean('featured'),
        ];

        $query = $this->publishedVehiclesQuery();

        if ($filters['make'] !== '') {
            $query->whereHas('make', fn ($makeQuery) => $makeQuery->where('name', $filters['make']));
        }

        if ($filters['model'] !== '') {
            $query->whereHas('model', fn ($modelQuery) => $modelQuery->where('name', $filters['model']));
        }

        if ($filters['province'] !== '') {
            $query->where(function ($provinceQuery) use ($filters): void {
                $provinceQuery
                    ->where('province', $filters['province'])
                    ->orWhere('state', $filters['province']);
            });
        }

        if ($filters['features'] !== []) {
            $query->where(function ($featureQuery) use ($filters): void {
                foreach ($filters['features'] as $feature) {
                    $featureQuery->orWhereJsonContains('features', $feature);
                }
            });
        }

        if ($filters['min_price']) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if ($filters['max_price']) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if ($filters['min_year']) {
            $query->where('year', '>=', $filters['min_year']);
        }

        if ($filters['max_year']) {
            $query->where('year', '<=', $filters['max_year']);
        }

        if ($filters['offers']) {
            $query->whereNotNull('original_price')->whereColumn('original_price', '>', 'price');
        }

        if ($filters['featured']) {
            $query->where('is_featured', true);
        }

        $vehicles = $query->paginate(9)->withQueryString();
        $exchangeQuote = $this->exchangeRateService->latest();
        $filterOptions = $this->filterOptions();

        return view('catalog.index', [
            'props' => [
                'homeUrl' => route('home'),
                'brandsUrl' => route('brands.index'),
                'accountUrl' => $this->resolveAccountUrl(),
                'sellUrl' => $this->resolveSellUrl(),
                'catalogUrl' => route('catalog.index'),
                'comparisonsUrl' => route('buyer.comparisons.index'),
                'valuationUrl' => route('valuation.index'),
                'loginUrl' => auth()->check() ? $this->resolveAccountUrl() : route('login'),
                'authUser' => $this->authUserPayload(),
                'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
                'vehicles' => $this->mapVehiclePaginator($vehicles, $exchangeQuote),
                'filters' => $filters,
                'filterOptions' => $filterOptions,
                'engagement' => $this->engagementPayload(),
                'endpoints' => $this->engagementEndpoints(),
                'footerLinks' => $this->footerLinks(),
            ],
        ]);
    }

    public function show(Vehicle $vehicle)
    {
        if (! auth()->check() || auth()->id() !== $vehicle->user_id) {
            if ($vehicle->status === 'published' && (! $vehicle->expires_at || $vehicle->expires_at->isFuture())) {
                $vehicle->increment('view_count');
                $vehicle->refresh();
            }
        }

        $vehicle->load(['make', 'model', 'media', 'owner']);
        $exchangeQuote = $this->exchangeRateService->latest();
        $isAvailable = $vehicle->status === 'published' && (! $vehicle->expires_at || $vehicle->expires_at->isFuture());

        $related = $this->publishedVehiclesQuery()
            ->whereKeyNot($vehicle->getKey())
            ->where(function ($query) use ($vehicle): void {
                $query->where('vehicle_make_id', $vehicle->vehicle_make_id)
                    ->orWhere('body_type', $vehicle->body_type);
            })
            ->take($isAvailable ? 4 : 8)
            ->get();

        return view('catalog.show', [
            'props' => [
                'homeUrl' => route('home'),
                'brandsUrl' => route('brands.index'),
                'accountUrl' => $this->resolveAccountUrl(),
                'sellUrl' => $this->resolveSellUrl(),
                'catalogUrl' => route('catalog.index'),
                'comparisonsUrl' => route('buyer.comparisons.index'),
                'valuationUrl' => route('valuation.index'),
                'loginUrl' => auth()->check() ? $this->resolveAccountUrl() : route('login'),
                'authUser' => $this->authUserPayload(),
                'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
                'isAvailable' => $isAvailable,
                'vehicle' => $this->mapVehicle($vehicle, $exchangeQuote),
                'relatedVehicles' => $related->map(fn (Vehicle $item) => $this->mapVehicle($item, $exchangeQuote))->values(),
                'engagement' => $this->engagementPayload(),
                'endpoints' => $this->engagementEndpoints(),
                'footerLinks' => $this->footerLinks(),
            ],
        ]);
    }

    public function comparisons(Request $request)
    {
        $comparisonIds = collect($request->input('ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->take(4)
            ->values();

        $exchangeQuote = $this->exchangeRateService->latest();
        $comparisonVehicles = $comparisonIds->isEmpty()
            ? collect()
            : $this->publishedVehiclesQuery()
                ->whereIn('id', $comparisonIds->all())
                ->get()
                ->sortBy(fn (Vehicle $vehicle) => $comparisonIds->search($vehicle->id))
                ->values();

        $suggestedVehicles = $this->publishedVehiclesQuery()
            ->when($comparisonVehicles->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $comparisonVehicles->pluck('id')))
            ->take(6)
            ->get();

        return view('catalog.comparisons', [
            'props' => [
                'homeUrl' => route('home'),
                'brandsUrl' => route('brands.index'),
                'accountUrl' => $this->resolveAccountUrl(),
                'sellUrl' => $this->resolveSellUrl(),
                'catalogUrl' => route('catalog.index'),
                'comparisonsUrl' => route('buyer.comparisons.index'),
                'valuationUrl' => route('valuation.index'),
                'loginUrl' => auth()->check() ? $this->resolveAccountUrl() : route('login'),
                'authUser' => $this->authUserPayload(),
                'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
                'comparisonIds' => $comparisonIds->all(),
                'comparisonVehicles' => $comparisonVehicles->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote))->values()->all(),
                'comparisonRecommendation' => $this->comparisonRecommendation($comparisonVehicles),
                'suggestedVehicles' => $suggestedVehicles->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote))->values()->all(),
                'footerLinks' => $this->footerLinks(),
            ],
        ]);
    }

    public function contactViaWhatsApp(Request $request, Vehicle $vehicle)
    {
        abort_unless(
            $vehicle->status === 'published' && (! $vehicle->expires_at || $vehicle->expires_at->isFuture()),
            404
        );

        $vehicle->loadMissing('owner');

        $contactPhoneRaw = $vehicle->owner?->whatsapp_phone ?: $vehicle->owner?->phone;
        $destination = $vehicle->owner?->whatsappDestination($contactPhoneRaw) ?: $this->normalizePhoneForCountry($contactPhoneRaw, $vehicle->owner?->country_code);
        abort_if(blank($destination), 404);

        $sessionKey = sprintf('vehicle_whatsapp_click.%s.%s', $vehicle->id, now()->toDateString());
        if (! $request->session()->has($sessionKey)) {
            $vehicle->increment('lead_count');
            $request->session()->put($sessionKey, true);
        }

        $text = trim((string) $request->query('text', ''));
        if ($text === '') {
            $text = 'Hola, me interesa '.$vehicle->title.' que vi en Movikaa.';
        }

        return redirect()->away(
            'https://wa.me/'.$destination.'?text='.rawurlencode($text)
        );
    }

    protected function publishedVehiclesQuery()
    {
        return Vehicle::query()
            ->with(['make', 'model', 'media'])
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->latest('published_at')
            ->latest();
    }

    protected function publishedVehiclesFilterQuery()
    {
        return Vehicle::query()
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    protected function filterOptions(): array
    {
        $baseQuery = $this->publishedVehiclesFilterQuery();

        $makes = VehicleMake::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $models = VehicleModel::query()
            ->active()
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $modelsByMake = VehicleMake::query()
            ->active()
            ->with(['models' => fn ($q) => $q->active()->orderBy('name')])
            ->get()
            ->mapWithKeys(fn ($make) => [
                $make->name => $make->models->pluck('name')->all()
            ])
            ->all();

        $provinceValues = (clone $baseQuery)
            ->whereNotNull('province')
            ->where('province', '!=', '')
            ->distinct()
            ->orderBy('province')
            ->pluck('province');

        $stateValues = (clone $baseQuery)
            ->whereNotNull('state')
            ->where('state', '!=', '')
            ->distinct()
            ->orderBy('state')
            ->pluck('state');

        $priceMinValue = (float) ((clone $baseQuery)->min('price') ?: 0);
        $priceMaxValue = (float) ((clone $baseQuery)->max('price') ?: 20000000);
        $yearMinValue = (int) ((clone $baseQuery)->min('year') ?: 1950);
        $yearMaxValue = (int) ((clone $baseQuery)->max('year') ?: now()->year + 1);

        $minPrice = (int) floor($priceMinValue / 500000) * 500000;
        $maxPrice = (int) ceil($priceMaxValue / 500000) * 500000;

        $featureOptions = VehicleFeatureOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'slug']);

        return [
            'makes' => $makes,
            'models' => $models,
            'modelsByMake' => $modelsByMake,
            'provinces' => $provinceValues
                ->merge($stateValues)
                ->filter()
                ->unique()
                ->sort()
                ->values()
                ->all(),
            'features' => $featureOptions->map(fn (VehicleFeatureOption $feature) => [
                'name' => $feature->name,
                'slug' => $feature->slug,
            ])->values()->all(),
            'priceRange' => [
                'min' => max(0, $minPrice),
                'max' => max(20000000, $maxPrice),
                'step' => 500000,
            ],
            'yearRange' => [
                'min' => (int) min(1950, $yearMinValue),
                'max' => (int) max(now()->year + 1, $yearMaxValue),
                'step' => 1,
            ],
        ];
    }

    protected function engagementPayload(): array
    {
        if (! auth()->check()) {
            return [
                'authenticated' => false,
                'favoriteVehicleIds' => [],
                'comparisonVehicleIds' => [],
            ];
        }

        $comparison = Comparison::query()->where('user_id', auth()->id())->latest()->first();

        return [
            'authenticated' => true,
            'favoriteVehicleIds' => auth()->user()->favorites()->pluck('vehicle_id')->all(),
            'comparisonVehicleIds' => $comparison?->vehicles()->pluck('vehicles.id')->all() ?? [],
        ];
    }

    protected function engagementEndpoints(): array
    {
        return [
            'favoriteTemplate' => route('buyer.favorites.store', ['vehicle' => '__VEHICLE__']),
            'comparisonTemplate' => route('buyer.comparisons.store', ['vehicle' => '__VEHICLE__']),
            'savedSearchUrl' => auth()->check() ? route('buyer.saved-searches.store') : null,
            'contactTemplate' => route('buyer.conversations.store', ['vehicle' => '__VEHICLE__']),
            'comparisonsUrl' => route('buyer.comparisons.index'),
            'loginUrl' => route('login'),
            'csrfToken' => csrf_token(),
        ];
    }

    protected function footerLinks(): array
    {
        return [
            'termsUrl' => route('legal.terms'),
            'privacyUrl' => route('legal.privacy'),
            'cookiesUrl' => route('legal.cookies'),
        ];
    }

    protected function resolveSellUrl(): string
    {
        return route('seller.onboarding.create');
    }

    protected function authUserPayload(): array
    {
        if (! auth()->check()) {
            return [
                'authenticated' => false,
            ];
        }

        $firstName = trim(strtok((string) auth()->user()->name, ' '));

        return [
            'authenticated' => true,
            'firstName' => $firstName !== '' ? $firstName : 'Cuenta',
            'dashboardUrl' => $this->resolveAccountUrl(),
            'buyerUrl' => route('buyer.dashboard'),
        ];
    }

    protected function resolveAccountUrl(): string
    {
        if (! auth()->check()) {
            return route('login');
        }

        if (auth()->user()->hasRole('admin')) {
            return route('admin.dashboard');
        }

        if (auth()->user()->hasRole('seller', 'dealer')) {
            return route('seller.dashboard');
        }

        return route('buyer.dashboard');
    }

    protected function mapVehiclePaginator(LengthAwarePaginator $paginator, array $exchangeQuote): array
    {
        return [
            'data' => $paginator->getCollection()->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    protected function mapVehicle(Vehicle $vehicle, array $exchangeQuote): array
    {
        $vehicle->loadMissing(['make', 'model', 'media', 'owner']);

        $media = $vehicle->media
            ->sortBy([['is_primary', 'desc'], ['sort_order', 'asc']])
            ->values()
            ->map(function ($item) use ($vehicle): array {
                $url = $item->path ? Storage::disk($item->disk ?: 'public')->url($item->path) : null;
                $thumb = data_get($item->conversions, 'thumb');
                $thumbUrl = $thumb ? Storage::disk($item->disk ?: 'public')->url($thumb) : $url;

                return [
                    'id' => $item->id,
                    'url' => $url,
                    'thumb_url' => $thumbUrl,
                    'alt' => $item->alt_text ?: $vehicle->title,
                    'is_primary' => (bool) $item->is_primary,
                ];
            });

        $pricing = VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote);
        $contactPhoneRaw = $vehicle->owner?->whatsapp_phone ?: $vehicle->owner?->phone;
        $contactPhone = $vehicle->owner?->formatPhone($contactPhoneRaw) ?: $contactPhoneRaw;
        $whatsAppNumber = $vehicle->owner?->whatsappDestination($contactPhoneRaw) ?: $this->normalizePhoneForCountry($contactPhoneRaw, $vehicle->owner?->country_code);
        $whatsAppUrl = $whatsAppNumber
            ? 'https://wa.me/'.$whatsAppNumber.'?text='.rawurlencode('Hola, me interesa '.$vehicle->title.' que vi en Movikaa.')
            : null;
        $contactUrl = $whatsAppNumber ? route('catalog.contact-whatsapp', $vehicle->slug) : null;
        $performanceBadge = null;

        if ((int) $vehicle->lead_count >= 3) {
            $performanceBadge = 'Con más contactos';
        } elseif ((int) $vehicle->view_count >= 25) {
            $performanceBadge = 'Más visto';
        }

        return [
            'id' => $vehicle->id,
            'title' => $vehicle->title,
            'slug' => $vehicle->slug,
            'url' => route('catalog.show', $vehicle->slug),
            'make' => $vehicle->make?->name,
            'model' => $vehicle->model?->name,
            'year' => $vehicle->year,
            'price' => $pricing['primary_formatted'],
            'price_secondary' => $pricing['secondary_formatted'],
            'price_raw' => $pricing['primary_raw'],
            'price_value' => (float) $vehicle->price,
            'city' => $vehicle->city,
            'province' => $vehicle->province ?: $vehicle->state,
            'description' => $vehicle->description,
            'body_type' => $vehicle->body_type,
            'fuel_type' => $vehicle->fuel_type,
            'transmission' => $vehicle->transmission,
            'mileage' => $vehicle->mileage,
            'mileage_unit' => $vehicle->mileage_unit,
            'condition' => $vehicle->condition,
            'publication_tier' => $vehicle->publication_tier,
            'is_featured' => (bool) $vehicle->is_featured,
            'price_badge' => $vehicle->price_badge,
            'features' => collect($vehicle->features)->filter()->values(),
            'media' => $media,
            'primary_image' => $media->first()['url'] ?? 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1400&q=80',
            'published_label' => optional($vehicle->published_at)->diffForHumans() ?? 'Recién publicado',
            'visibility_bucket' => data_get($vehicle->metadata, 'visibility_bucket', 'standard'),
            'plan_name' => data_get($vehicle->metadata, 'plan_name', 'Basico'),
            'is_paid' => (bool) data_get($vehicle->metadata, 'plan_is_paid', false),
            'seller_name' => $vehicle->owner?->name ?: 'Vendedor Movikaa',
            'contact_phone' => $contactPhone,
            'contact_url' => $contactUrl,
            'whatsapp_url' => $whatsAppUrl,
            'view_count' => (int) $vehicle->view_count,
            'lead_count' => (int) $vehicle->lead_count,
            'performance_badge' => $performanceBadge,
            'is_owner' => auth()->check() && auth()->id() === $vehicle->user_id,
        ];
    }

    protected function normalizePhoneForCountry(?string $phone, ?string $countryCode = 'CR'): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        $codes = \App\Models\User::dialingCodes();
        $dial = $codes[strtoupper((string) $countryCode)] ?? '506';

        foreach (collect($codes)->sortByDesc(fn (string $code) => strlen($code)) as $knownCode) {
            if (str_starts_with($digits, $knownCode)) {
                return $digits;
            }
        }

        if (str_starts_with($digits, $dial)) {
            return $digits;
        }

        return $dial.$digits;
    }

    protected function comparisonRecommendation(Collection $vehicles): ?array
    {
        if ($vehicles->count() < 2) {
            return null;
        }

        $priceMin = (float) $vehicles->min('price');
        $priceMax = (float) $vehicles->max('price');
        $yearMin = (int) $vehicles->min('year');
        $yearMax = (int) $vehicles->max('year');
        $mileageMin = (float) ($vehicles->filter(fn (Vehicle $vehicle) => $vehicle->mileage !== null)->min('mileage') ?? 0);
        $mileageMax = (float) ($vehicles->filter(fn (Vehicle $vehicle) => $vehicle->mileage !== null)->max('mileage') ?? 0);
        $averagePrice = (float) $vehicles->avg('price');
        $averageMileage = (float) ($vehicles->filter(fn (Vehicle $vehicle) => $vehicle->mileage !== null)->avg('mileage') ?? 0);

        $ranking = $vehicles->map(function (Vehicle $vehicle) use ($priceMin, $priceMax, $yearMin, $yearMax, $mileageMin, $mileageMax, $averagePrice, $averageMileage): array {
            $priceScore = $this->inverseScore((float) $vehicle->price, $priceMin, $priceMax);
            $yearScore = $this->directScore((float) ($vehicle->year ?? $yearMin), $yearMin, $yearMax);
            $mileageValue = $vehicle->mileage !== null ? (float) $vehicle->mileage : ($mileageMax ?: 0);
            $mileageScore = $this->inverseScore($mileageValue, $mileageMin, $mileageMax ?: max(1, $mileageValue));
            $bonusScore = in_array($vehicle->fuel_type, ['Híbrido', 'Eléctrico', 'PHEV'], true) ? 8 : 0;
            $score = (int) round(($priceScore * 0.4) + ($yearScore * 0.32) + ($mileageScore * 0.2) + $bonusScore);
            $reasons = [];

            if ((float) $vehicle->price === $priceMin) {
                $reasons[] = 'es la opción más económica del grupo';
            } elseif ((float) $vehicle->price <= $averagePrice) {
                $reasons[] = 'se mantiene por debajo del precio promedio';
            }

            if ((int) $vehicle->year === $yearMax) {
                $reasons[] = 'está entre los años más recientes';
            }

            if ($vehicle->mileage !== null) {
                if ((float) $vehicle->mileage === $mileageMin) {
                    $reasons[] = 'tiene el kilometraje más bajo';
                } elseif ($averageMileage > 0 && (float) $vehicle->mileage <= $averageMileage) {
                    $reasons[] = 'su kilometraje está por debajo del promedio';
                }
            }

            if (in_array($vehicle->fuel_type, ['Híbrido', 'Eléctrico', 'PHEV'], true)) {
                $reasons[] = 'ofrece una motorización más eficiente';
            }

            if ($reasons === []) {
                $reasons[] = 'mantiene un balance sólido entre precio, año y kilometraje';
            }

            return [
                'vehicle_id' => $vehicle->id,
                'title' => $vehicle->title,
                'url' => route('catalog.show', $vehicle->slug),
                'headline' => $this->recommendationHeadline($vehicle, $priceMin, $yearMax, $mileageMin),
                'score' => max(1, min(100, $score)),
                'reasons' => array_values(array_unique(array_slice($reasons, 0, 3))),
            ];
        })->sortByDesc('score')->values();

        return [
            'winner' => $ranking->first(),
            'runnerUp' => $ranking->skip(1)->first(),
            'ranking' => $ranking->all(),
        ];
    }

    protected function directScore(float $value, float $min, float $max): float
    {
        if ($max <= $min) {
            return 100;
        }

        return (($value - $min) / ($max - $min)) * 100;
    }

    protected function inverseScore(float $value, float $min, float $max): float
    {
        if ($max <= $min) {
            return 100;
        }

        return (1 - (($value - $min) / ($max - $min))) * 100;
    }

    protected function recommendationHeadline(Vehicle $vehicle, float $priceMin, int $yearMax, float $mileageMin): string
    {
        if ((float) $vehicle->price === $priceMin) {
            return 'Destaca por precio';
        }

        if ((int) $vehicle->year === $yearMax) {
            return 'Destaca por año';
        }

        if ($vehicle->mileage !== null && (float) $vehicle->mileage === $mileageMin) {
            return 'Destaca por kilometraje';
        }

        return 'La opción más equilibrada';
    }
}
