<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Services\Currency\ExchangeRateService;
use App\Services\Valuation\ValuationSettingsService;
use App\Support\VehiclePricePresenter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $valuationSettings,
    ) {
    }

    public function index()
    {
        $publishedVehicles = collect();
        $exchangeQuote = $this->exchangeRateService->latest();

        if (Schema::hasTable('vehicles')) {
            $publishedVehicles = Vehicle::query()
                ->with(['make', 'model', 'media'])
                ->where('status', 'published')
                ->where(function ($query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
                })
                ->latest('published_at')
                ->latest()
                ->take(18)
                ->get();
        }

        $featuredPaid = $publishedVehicles
            ->filter(fn (Vehicle $vehicle) => (bool) data_get($vehicle->metadata, 'plan_is_paid', false))
            ->sortByDesc(fn (Vehicle $vehicle) => [
                (int) $vehicle->is_featured,
                (int) data_get($vehicle->metadata, 'plan_priority_weight', 0),
                optional($vehicle->published_at)?->timestamp ?? 0,
            ])
            ->take(4)
            ->values()
            ->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote));

        $recentVehicles = $publishedVehicles
            ->take(4)
            ->values()
            ->map(fn (Vehicle $vehicle) => $this->mapVehicle($vehicle, $exchangeQuote));

        $catalogMakes = collect();

        if (Schema::hasTable('vehicle_makes')) {
            $catalogMakes = VehicleMake::query()
                ->active()
                ->with(['models' => fn ($query) => $query->active()->orderBy('name')])
                ->orderBy('name')
                ->get()
                ->map(fn (VehicleMake $make) => [
                    'id' => $make->id,
                    'name' => $make->name,
                    'slug' => $make->slug,
                    'models' => $make->models->map(fn ($model) => [
                        'id' => $model->id,
                        'name' => $model->name,
                        'slug' => $model->slug,
                    ])->values()->all(),
                ])
                ->values();
        }

        $cities = $publishedVehicles
            ->pluck('city')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $priceCeiling = (int) max(20000000, (int) ceil(((float) $publishedVehicles->max('price') ?: 20000000) / 500000) * 500000);

        return view('home', [
            'featuredPaidVehicles' => $featuredPaid,
            'recentVehicles' => $recentVehicles,
            'catalogMakes' => $catalogMakes,
            'catalogCities' => $cities,
            'catalogPriceCeiling' => $priceCeiling,
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
        ]);
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
        $pricing = VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $exchangeQuote);

        return [
            'id' => $vehicle->id,
            'title' => $vehicle->title,
            'price' => $pricing['primary_formatted'],
            'price_secondary' => $pricing['secondary_formatted'],
            'price_raw' => $pricing['primary_raw'],
            'meta' => collect([$vehicle->fuel_type, $vehicle->body_type, $vehicle->transmission])->filter()->implode(' | '),
            'image' => $image,
            'url' => route('catalog.show', $vehicle->slug),
            'city' => $vehicle->city,
            'published_label' => optional($vehicle->published_at)->diffForHumans() ?? 'Recien publicado',
            'visibility_bucket' => data_get($vehicle->metadata, 'visibility_bucket', 'standard'),
            'plan_name' => data_get($vehicle->metadata, 'plan_name', 'Basico'),
            'is_paid' => (bool) data_get($vehicle->metadata, 'plan_is_paid', false),
            'is_featured' => (bool) $vehicle->is_featured,
        ];
    }
}

