<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Services\Currency\ExchangeRateService;
use App\Services\Seo\SeoService;
use App\Services\Valuation\ValuationSettingsService;
use App\Support\VehiclePricePresenter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly SeoService $seoService,
        private readonly ValuationSettingsService $valuationSettings,
    ) {
    }

    public function index()
    {
        $publishedVehicles = collect();
        $exchangeQuote = $this->exchangeRateService->latest();

        if (Schema::hasTable('vehicles')) {
            $publishedVehicles = $this->publishedVehiclesQuery()
                ->take(24)
                ->get();
        }

        $recentVehicles = $publishedVehicles
            ->sortByDesc(fn (Vehicle $vehicle) => optional($vehicle->published_at)?->timestamp ?? 0)
            ->take(6)
            ->values()
            ->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote));

        $featuredVehicles = $publishedVehicles
            ->sortByDesc(fn (Vehicle $vehicle) => [
                (int) $vehicle->is_featured,
                (int) data_get($vehicle->metadata, 'plan_priority_weight', 0),
                optional($vehicle->published_at)?->timestamp ?? 0,
            ])
            ->take(4)
            ->values()
            ->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote));

        $offerVehicles = $publishedVehicles
            ->filter(fn (Vehicle $vehicle) => $vehicle->original_price && (float) $vehicle->original_price > (float) $vehicle->price)
            ->sortByDesc(fn (Vehicle $vehicle) => ((float) $vehicle->original_price - (float) $vehicle->price))
            ->take(4)
            ->values()
            ->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote) + [
                'original_price' => VehiclePricePresenter::present((float) $vehicle->original_price, $vehicle->currency, $exchangeQuote)['primary_formatted'],
            ]);

        $catalogMakes = collect();

        if (Schema::hasTable('vehicle_makes')) {
            $catalogMakes = VehicleMake::query()
                ->active()
                ->with(['models' => fn ($query) => $query->active()->orderBy('name')])
                ->withCount(['vehicles as listings_count' => function ($query): void {
                    $query->where('status', 'published')
                        ->where(function ($inner): void {
                            $inner->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                        });
                }])
                ->orderByDesc('listings_count')
                ->orderBy('name')
                ->get()
                ->map(fn (VehicleMake $make) => [
                    'id' => $make->id,
                    'name' => $make->name,
                    'slug' => $make->slug,
                    'listings_count' => (int) $make->listings_count,
                    'models' => $make->models->map(fn ($model) => [
                        'id' => $model->id,
                        'name' => $model->name,
                        'slug' => $model->slug,
                    ])->values()->all(),
                ])
                ->values();
        }

        $catalogProvinces = config('vehicle.provinces', []);

        $priceCeiling = (int) max(20000000, (int) ceil(((float) $publishedVehicles->max('price') ?: 20000000) / 500000) * 500000);
        $yearFloor = 1950;
        $yearCeiling = (int) max(now()->year + 1, (int) ($publishedVehicles->max('year') ?: now()->year + 1));

        return view('home', [
            'recentVehicles' => $recentVehicles,
            'featuredVehicles' => $featuredVehicles,
            'offerVehicles' => $offerVehicles,
            'catalogMakes' => $catalogMakes,
            'catalogProvinces' => $catalogProvinces,
            'catalogPriceCeiling' => $priceCeiling,
            'catalogYearRange' => [
                'min' => $yearFloor,
                'max' => $yearCeiling,
            ],
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'seoData' => $this->seoService->forHome(request()),
        ]);
    }

    public function brands()
    {
        $makes = VehicleMake::query()
            ->active()
            ->withCount(['models' => fn ($query) => $query->active()])
            ->withCount(['vehicles as listings_count' => function ($query): void {
                $query->where('status', 'published')
                    ->where(function ($inner): void {
                        $inner->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                    });
            }])
            ->orderByDesc('listings_count')
            ->orderBy('name')
            ->get()
            ->map(fn (VehicleMake $make) => [
                'id' => $make->id,
                'name' => $make->name,
                'slug' => $make->slug,
                'models_count' => (int) $make->models_count,
                'listings_count' => (int) $make->listings_count,
            ])
            ->values();

        return view('brands.index', [
            'props' => [
                'homeUrl' => route('home'),
                'catalogUrl' => route('catalog.index'),
                'brandsUrl' => route('brands.index'),
                'valuationUrl' => route('valuation.index'),
                'sellUrl' => route('seller.onboarding.create'),
                'accountUrl' => auth()->check()
                    ? (auth()->user()->hasRole('admin')
                        ? route('admin.dashboard')
                        : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
                    : route('login'),
                'loginUrl' => route('login'),
                'authUser' => auth()->check() ? [
                    'authenticated' => true,
                    'firstName' => trim(strtok((string) auth()->user()->name, ' ')) ?: 'Cuenta',
                    'dashboardUrl' => auth()->user()->hasRole('admin')
                        ? route('admin.dashboard')
                        : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')),
                    'buyerUrl' => route('buyer.dashboard'),
                ] : ['authenticated' => false],
                'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
                'makes' => $makes,
                'footerLinks' => [
                    'termsUrl' => route('legal.terms'),
                    'privacyUrl' => route('legal.privacy'),
                    'cookiesUrl' => route('legal.cookies'),
                ],
            ],
            'seoData' => $this->seoService->forBrandsIndex(request()),
        ]);
    }

    private function publishedVehiclesQuery()
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

    private function mapVehicle(Vehicle $vehicle, array $exchangeQuote): array
    {
        $media = $vehicle->media
            ->sortBy([['is_primary', 'desc'], ['sort_order', 'asc']])
            ->values();

        $primary = $media->first();
        $image = $primary && $primary->path
            ? Storage::disk($primary->disk ?: 'public')->url($primary->path)
            : 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1400&q=80';
        $imageThumb = $primary?->thumbUrl();
        $pricing = VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote);

        return [
            'id' => $vehicle->id,
            'title' => $vehicle->title,
            'price' => $pricing['primary_formatted'],
            'price_secondary' => $pricing['secondary_formatted'],
            'price_raw' => $pricing['primary_raw'],
            'image' => $image,
            'image_thumb' => $imageThumb,
            'image_width' => $primary?->width,
            'image_height' => $primary?->height,
            'image_thumb_width' => data_get($primary?->conversions, 'thumb_width'),
            'image_thumb_height' => data_get($primary?->conversions, 'thumb_height'),
            'url' => route('catalog.show', $vehicle->slug),
            'city' => $vehicle->city,
            'province' => $vehicle->province ?: $vehicle->state,
            'published_label' => optional($vehicle->published_at)->diffForHumans() ?? 'Recién publicado',
            'make' => $vehicle->make?->name,
            'model' => $vehicle->model?->name,
            'year' => $vehicle->year,
            'is_featured' => (bool) $vehicle->is_featured,
        ];
    }
}
