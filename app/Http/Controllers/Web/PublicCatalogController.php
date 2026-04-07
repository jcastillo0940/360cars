<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Services\Currency\ExchangeRateService;
use App\Services\Valuation\ValuationSettingsService;
use App\Support\VehiclePricePresenter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
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
            'city' => $request->string('city')->toString(),
            'features' => collect($request->input('features', []))->filter()->map(fn ($feature) => (string) $feature)->values()->all(),
            'min_price' => $request->integer('min_price') ?: null,
            'max_price' => $request->integer('max_price') ?: null,
            'min_year' => $request->integer('min_year') ?: null,
            'max_year' => $request->integer('max_year') ?: null,
        ];

        $query = $this->publishedVehiclesQuery();

        if ($filters['make'] !== '') {
            $query->whereHas('make', fn ($makeQuery) => $makeQuery->where('name', $filters['make']));
        }

        if ($filters['model'] !== '') {
            $query->whereHas('model', fn ($modelQuery) => $modelQuery->where('name', $filters['model']));
        }

        if ($filters['city'] !== '') {
            $query->where('city', $filters['city']);
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

        $vehicles = $query->paginate(9)->withQueryString();
        $exchangeQuote = $this->exchangeRateService->latest();
        $filterOptions = $this->filterOptions();

        return view('catalog.index', [
            'props' => [
                'homeUrl' => route('home'),
                'accountUrl' => $this->resolveAccountUrl(),
                'sellUrl' => $this->resolveSellUrl(),
                'catalogUrl' => route('catalog.index'),
                'comparisonsUrl' => auth()->check() ? route('buyer.comparisons.index') : route('login', ['redirect' => route('buyer.comparisons.index')]),
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
        abort_unless($vehicle->status === 'published' && (! $vehicle->expires_at || $vehicle->expires_at->isFuture()), 404);

        if (! auth()->check() || auth()->id() !== $vehicle->user_id) {
            $vehicle->increment('view_count');
            $vehicle->refresh();
        }

        $vehicle->load(['make', 'model', 'media', 'owner']);
        $exchangeQuote = $this->exchangeRateService->latest();

        $related = $this->publishedVehiclesQuery()
            ->whereKeyNot($vehicle->getKey())
            ->where(function ($query) use ($vehicle): void {
                $query->where('vehicle_make_id', $vehicle->vehicle_make_id)
                    ->orWhere('body_type', $vehicle->body_type);
            })
            ->take(4)
            ->get();

        return view('catalog.show', [
            'props' => [
                'homeUrl' => route('home'),
                'accountUrl' => $this->resolveAccountUrl(),
                'sellUrl' => $this->resolveSellUrl(),
                'catalogUrl' => route('catalog.index'),
                'comparisonsUrl' => auth()->check() ? route('buyer.comparisons.index') : route('login', ['redirect' => route('buyer.comparisons.index')]),
                'valuationUrl' => route('valuation.index'),
                'loginUrl' => auth()->check() ? $this->resolveAccountUrl() : route('login'),
                'authUser' => $this->authUserPayload(),
                'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
                'vehicle' => $this->mapVehicle($vehicle, $exchangeQuote),
                'relatedVehicles' => $related->map(fn (Vehicle $item) => $this->mapVehicle($item, $exchangeQuote))->values(),
                'engagement' => $this->engagementPayload(),
                'endpoints' => $this->engagementEndpoints(),
                'footerLinks' => $this->footerLinks(),
            ],
        ]);
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

    protected function filterOptions(): array
    {
        $vehicles = $this->publishedVehiclesQuery()->get();
        $makes = $vehicles->pluck('make')->filter()->unique('id')->sortBy('name')->values();
        $modelsByMake = $makes->mapWithKeys(function ($make) use ($vehicles) {
            return [
                $make->name => $vehicles
                    ->filter(fn ($vehicle) => $vehicle->make?->name === $make->name && $vehicle->model?->name)
                    ->pluck('model.name')
                    ->unique()
                    ->sort()
                    ->values()
                    ->all(),
            ];
        });

        $minPrice = (int) floor(((float) $vehicles->min('price') ?: 0) / 500000) * 500000;
        $maxPrice = (int) ceil(((float) $vehicles->max('price') ?: 20000000) / 500000) * 500000;

        $featureOptions = VehicleFeatureOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['name', 'slug']);

        return [
            'makes' => $makes->pluck('name')->all(),
            'models' => $vehicles->pluck('model.name')->filter()->unique()->sort()->values()->all(),
            'modelsByMake' => $modelsByMake,
            'cities' => $vehicles->pluck('city')->filter()->unique()->sort()->values()->all(),
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
                'min' => (int) ($vehicles->min('year') ?: 2000),
                'max' => (int) ($vehicles->max('year') ?: now()->year),
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
        if (! auth()->check()) {
            return route('seller.onboarding.create');
        }

        return auth()->user()->hasRole('seller', 'dealer', 'admin')
            ? route('seller.dashboard')
            : route('seller.onboarding.create');
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
                $thumb = data_get($item->conversions, 'thumb_path');
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
        $contactPhone = $vehicle->owner?->whatsapp_phone ?: $vehicle->owner?->phone;
        $whatsAppUrl = $contactPhone
            ? 'https://wa.me/'.$this->normalizeCostaRicaPhone($contactPhone).'?text='.rawurlencode('Hola, me interesa '.$vehicle->title.' que vi en Movikaa.')
            : null;

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
            'city' => $vehicle->city,
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
            'published_label' => optional($vehicle->published_at)->diffForHumans() ?? 'Recien publicado',
            'visibility_bucket' => data_get($vehicle->metadata, 'visibility_bucket', 'standard'),
            'plan_name' => data_get($vehicle->metadata, 'plan_name', 'Basico'),
            'is_paid' => (bool) data_get($vehicle->metadata, 'plan_is_paid', false),
            'seller_name' => $vehicle->owner?->name ?: 'Vendedor Movikaa',
            'contact_phone' => $contactPhone,
            'whatsapp_url' => $whatsAppUrl,
        ];
    }

    protected function normalizeCostaRicaPhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (str_starts_with($digits, '506')) {
            return $digits;
        }

        return '506'.$digits;
    }
}








