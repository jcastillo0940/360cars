<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\UploadVehicleMediaRequest;
use App\Http\Requests\Vehicle\UpsertVehicleRequest;
use App\Models\LifestyleCategory;
use App\Models\Plan;
use App\Models\Province;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Models\Conversation;
use App\Models\VehicleFeatureOption;
use App\Models\VehicleMake;
use App\Models\VehicleMedia;
use App\Services\Currency\ExchangeRateService;
use App\Services\Media\VehicleImageProcessor;
use App\Services\Publication\PublicationLifecycleService;
use App\Services\Publication\PublicationLimitGuard;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerPortalController extends Controller
{
    public function __construct(
        private readonly VehicleImageProcessor $imageProcessor,
        private readonly PublicationLimitGuard $limitGuard,
        private readonly PublicationLifecycleService $lifecycleService,
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $settingsService,
    ) {
    }

    public function index(Request $request)
    {
        return view('portal.seller.overview', $this->overviewData($request));
    }

    public function listings(Request $request)
    {
        return view('portal.seller.listings', $this->listingData($request));
    }

    public function createPage(Request $request)
    {
        return redirect()
            ->route('seller.onboarding.create')
            ->with('status', 'Usa este flujo guiado para publicar tu auto con una mejor experiencia.');
    }

    public function editPage(Request $request, Vehicle $vehicle)
    {
        $this->authorizeVehicleAccess($request, $vehicle);

        return view('portal.seller.edit', $this->formData($request, $vehicle));
    }

    public function mediaPage(Request $request)
    {
        return view('portal.seller.media', $this->mediaData($request));
    }

    public function billingPage(Request $request)
    {
        return view('portal.seller.billing', $this->billingData($request));
    }

    public function messagesPage(Request $request)
    {
        $data = $this->baseData($request);

        return view('portal.seller.messages', $data + [
            'conversationList' => Conversation::query()
                ->with(['vehicle', 'participants'])
                ->whereHas('participants', fn ($query) => $query->where('users.id', $request->user()->id))
                ->whereHas('vehicle', fn ($query) => $query->where('user_id', $request->user()->id))
                ->latest('last_message_at')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(UpsertVehicleRequest $request): RedirectResponse
    {
        $user = $request->user();
        $publicationTier = $request->input('publication_tier', 'basic');
        $this->limitGuard->ensureCanUseTier($user, $publicationTier);
        $this->assertImageLimit($user, null, $this->incomingImageCount($request));

        if ($request->input('status') === 'published') {
            $this->limitGuard->ensureCanPublish($user);
        }

        $queuedMediaIds = [];

        $vehicle = DB::transaction(function () use ($request, $user, &$queuedMediaIds) {
            $data = $this->payload($request);
            $data['user_id'] = $user->id;
            $data['slug'] = $this->uniqueSlug($request->string('title')->toString(), (int) $request->input('year'));

            if (($data['status'] ?? 'draft') === 'published') {
                $data['published_at'] = now();
            }

            $vehicle = Vehicle::create($data);
            $this->syncLifestyleCategories($vehicle, $request->input('lifestyle_category_ids', []));
            $vehicle = $this->lifecycleService->applyPlanBenefits($user, $vehicle, $data['status'] ?? 'draft');
            $queuedMediaIds = $this->queueMediaFromRequest($request, $vehicle, false);

            return $vehicle;
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        if (($vehicle->status ?? 'draft') === 'published') {
            return redirect()->route('catalog.show', $vehicle->slug)->with('status', 'Publicacion creada y visible en el marketplace.');
        }

        return redirect()->route('seller.dashboard')->with('status', "Publicacion {$vehicle->title} creada correctamente. Ya puedes editarla a detalle desde Publicaciones.");
    }

    public function update(UpsertVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $user = $request->user();
        $tier = $request->input('publication_tier', $vehicle->publication_tier);
        $this->limitGuard->ensureCanUseTier($user, $tier);
        $this->assertImageLimit($user, $vehicle, $this->incomingImageCount($request));

        $targetStatus = $request->input('status', $vehicle->status);
        if ($targetStatus === 'published' && $vehicle->status !== 'published') {
            $this->limitGuard->ensureCanPublish($user, $vehicle);
        }

        $queuedMediaIds = [];

        DB::transaction(function () use ($request, $vehicle, $user, &$queuedMediaIds, $targetStatus) {
            $data = $this->payload($request);

            if (array_key_exists('title', $data) || array_key_exists('year', $data)) {
                $data['slug'] = $this->uniqueSlug($data['title'] ?? $vehicle->title, (int) ($data['year'] ?? $vehicle->year), $vehicle->id);
            }

            if (($data['status'] ?? $vehicle->status) === 'published' && ! $vehicle->published_at) {
                $data['published_at'] = now();
            }

            $vehicle->update($data);

            if ($request->exists('lifestyle_category_ids')) {
                $this->syncLifestyleCategories($vehicle, $request->input('lifestyle_category_ids', []));
            }

            if (($targetStatus ?? $vehicle->status) === 'sold') {
                $vehicle->favorites()->delete();
            }

            $this->lifecycleService->applyPlanBenefits($user, $vehicle, $targetStatus);
            $queuedMediaIds = $this->queueMediaFromRequest($request, $vehicle, true);
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        if (($targetStatus ?? $vehicle->status) === 'published') {
            return redirect()->route('catalog.show', $vehicle->slug)->with('status', 'Publicacion actualizada y visible en el marketplace.');
        }

        return redirect()->route('seller.vehicles.edit', $vehicle)->with('status', 'Publicacion actualizada correctamente.');
    }

    public function publish(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->limitGuard->ensureCanUseTier($request->user(), $vehicle->publication_tier);
        $this->limitGuard->ensureCanPublish($request->user(), $vehicle);

        $vehicle->update(['status' => 'published', 'published_at' => $vehicle->published_at ?? now()]);
        $this->lifecycleService->applyPlanBenefits($request->user(), $vehicle, 'published');

        return redirect()->route('catalog.show', $vehicle->slug)->with('status', 'Publicacion publicada correctamente y visible en el marketplace.');
    }

    public function pause(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $vehicle->update(['status' => 'paused']);

        return back()->with('status', 'Publicacion pausada correctamente.');
    }

    public function refreshBasic(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->lifecycleService->refreshBasicPublication($request->user(), $vehicle);

        return back()->with('status', 'Tu anuncio basico fue renovado y volvio a posicionarse por 30 dias.');
    }

    public function destroy(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $vehicle->load('media');

        DB::transaction(function () use ($vehicle): void {
            $this->imageProcessor->deleteAllForVehicle($vehicle);
            $vehicle->favorites()->delete();
            $vehicle->comparisons()->detach();
            $vehicle->conversations()->each(function (Conversation $conversation): void {
                $conversation->messages()->delete();
                $conversation->participants()->detach();
                $conversation->delete();
            });
            $vehicle->delete();
        });

        return redirect()->route('seller.listings')->with('status', 'Publicacion eliminada correctamente.');
    }

    public function uploadMedia(UploadVehicleMediaRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->limitGuard->ensureCanUploadImages($request->user(), $vehicle->media()->count() + count($request->file('images', [])));

        $queuedMediaIds = $this->imageProcessor->stageMany($vehicle, $request->file('images'));
        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return back()->with('status', 'Imagenes agregadas y encoladas correctamente.');
    }

    public function makePrimary(Request $request, Vehicle $vehicle, VehicleMedia $media): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        abort_unless($media->vehicle_id === $vehicle->id, 404);
        $this->imageProcessor->makePrimary($vehicle, $media);

        return back()->with('status', 'Imagen principal actualizada.');
    }

    public function destroyMedia(Request $request, Vehicle $vehicle, VehicleMedia $media): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        abort_unless($media->vehicle_id === $vehicle->id, 404);
        $this->imageProcessor->delete($media);

        return back()->with('status', 'Imagen eliminada correctamente.');
    }

    public function replaceMedia(Request $request, Vehicle $vehicle, VehicleMedia $media): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        abort_unless($media->vehicle_id === $vehicle->id, 404);

        $validated = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $file = $validated['image'];
        $wasPrimary = (bool) $media->is_primary;
        $altText = $media->alt_text;
        $sortOrder = $media->sort_order;

        $replacement = $this->imageProcessor->stage($vehicle, $file, $wasPrimary);
        $replacement->forceFill([
            'alt_text' => $altText,
            'sort_order' => $sortOrder,
        ])->save();

        if ($wasPrimary) {
            $media->forceFill(['is_primary' => false])->save();
        }

        $this->imageProcessor->delete($media);
        $this->imageProcessor->dispatchMany([$replacement->id]);

        return back()->with('status', 'Fotografía reemplazada correctamente.');
    }

    private function overviewData(Request $request): array
    {
        $base = $this->baseData($request);
        $vehicles = $base['vehicles'];

        $statusBuckets = [
            ['label' => 'Borradores', 'value' => $vehicles->where('status', 'draft')->count()],
            ['label' => 'Publicadas', 'value' => $vehicles->where('status', 'published')->count()],
            ['label' => 'Pausadas', 'value' => $vehicles->where('status', 'paused')->count()],
            ['label' => 'Vencidas', 'value' => $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->expires_at && $vehicle->expires_at->isPast())->count()],
        ];

        $maxStatus = max(1, collect($statusBuckets)->max('value'));
        $inventoryStatusChart = collect($statusBuckets)->map(fn ($item) => $item + [
            'width' => (int) round(($item['value'] / $maxStatus) * 100),
        ]);

        $performanceRows = $vehicles
            ->sortByDesc(fn (Vehicle $vehicle) => ($vehicle->lead_count * 3) + $vehicle->view_count)
            ->take(6)
            ->values()
            ->map(function (Vehicle $vehicle): array {
                return [
                    'label' => Str::limit($vehicle->title, 24),
                    'leads' => (int) $vehicle->lead_count,
                    'views' => (int) $vehicle->view_count,
                    'status' => $vehicle->status,
                ];
            });

        $maxPerformance = max(1, $performanceRows->max(fn ($row) => max($row['leads'], $row['views'])));
        $listingPerformanceChart = $performanceRows->map(fn ($row) => $row + [
            'lead_height' => max(10, (int) round(($row['leads'] / $maxPerformance) * 100)),
            'view_height' => max(10, (int) round(($row['views'] / $maxPerformance) * 100)),
        ]);

        return $base + [
            'inventoryStatusChart' => $inventoryStatusChart,
            'listingPerformanceChart' => $listingPerformanceChart,
            'recentVehicles' => $vehicles->take(6),
        ];
    }

    private function listingData(Request $request): array
    {
        $base = $this->baseData($request);
        $user = $request->user();
        $filters = [
            'q' => trim($request->string('q')->toString()),
            'status' => $request->string('status')->toString(),
            'tier' => $request->string('tier')->toString(),
            'make' => $request->integer('make') ?: null,
            'city' => trim($request->string('city')->toString()),
            'year_from' => $request->integer('year_from') ?: null,
            'year_to' => $request->integer('year_to') ?: null,
            'sort' => $request->string('sort')->toString() ?: 'latest',
        ];

        $query = Vehicle::query()
            ->with(['make', 'model', 'media'])
            ->where('user_id', $user->id)
            ->when($filters['q'] !== '', function ($builder) use ($filters): void {
                $builder->where(function ($query) use ($filters): void {
                    $query->where('title', 'like', '%'.$filters['q'].'%')
                        ->orWhere('city', 'like', '%'.$filters['q'].'%')
                        ->orWhere('plate_number', 'like', '%'.$filters['q'].'%');
                });
            })
            ->when($filters['status'] !== '', fn ($builder) => $builder->where('status', $filters['status']))
            ->when($filters['tier'] !== '', fn ($builder) => $builder->where('publication_tier', $filters['tier']))
            ->when($filters['make'], fn ($builder) => $builder->where('vehicle_make_id', $filters['make']))
            ->when($filters['city'] !== '', fn ($builder) => $builder->where('city', 'like', '%'.$filters['city'].'%'))
            ->when($filters['year_from'], fn ($builder) => $builder->where('year', '>=', $filters['year_from']))
            ->when($filters['year_to'], fn ($builder) => $builder->where('year', '<=', $filters['year_to']));

        match ($filters['sort']) {
            'price_desc' => $query->orderByDesc('price'),
            'price_asc' => $query->orderBy('price'),
            'year_desc' => $query->orderByDesc('year'),
            'year_asc' => $query->orderBy('year'),
            'leads_desc' => $query->orderByDesc('lead_count')->orderByDesc('view_count'),
            'views_desc' => $query->orderByDesc('view_count')->orderByDesc('lead_count'),
            default => $query->latest(),
        };

        $sellerListings = $query->paginate(10)->withQueryString();

        $listingSummary = [
            'published' => (clone $query)->where('status', 'published')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
            'paused' => (clone $query)->where('status', 'paused')->count(),
            'expired' => (clone $query)->whereNotNull('expires_at')->where('expires_at', '<', now())->count(),
            'leads' => (clone $query)->sum('lead_count'),
            'contactos' => (clone $query)->sum('lead_count'),
            'views' => (clone $query)->sum('view_count'),
        ];

        return $base + [
            'sellerListings' => $sellerListings,
            'sellerFilters' => $filters,
            'listingSummary' => $listingSummary,
        ];
    }

    private function formData(Request $request, ?Vehicle $vehicle = null): array
    {
        $base = $this->baseData($request);

        if ($vehicle) {
            $vehicle->load(['make', 'model', 'lifestyleCategories', 'media']);
        }

        $featureOptions = VehicleFeatureOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $googleMapsKey = (string) $this->settingsService->get('integrations.google_maps.key', config('services.google_maps.key'));
        $locationTree = Province::query()
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
            ->all();

        $photoSlots = config('vehicle.photo_slots', []);
        $existingMediaBySlot = collect();

        if ($vehicle) {
            $existingMediaBySlot = $vehicle->media->groupBy(function (VehicleMedia $media) {
                return $media->alt_text ?: 'extra';
            });
        }

        return $base + [
            'editingVehicle' => $vehicle,
            'vehicleConditions' => config('vehicle.conditions', []),
            'vehicleBodyTypes' => config('vehicle.body_types', []),
            'vehicleFuelTypes' => config('vehicle.fuel_types', []),
            'vehicleTransmissions' => config('vehicle.transmissions', []),
            'vehicleDrivetrains' => config('vehicle.drivetrains', []),
            'vehicleCities' => config('vehicle.cities', []),
            'featureOptions' => $featureOptions,
            'photoSlots' => $photoSlots,
            'existingMediaBySlot' => $existingMediaBySlot,
            'locationTree' => $locationTree,
            'googleMapsKey' => $googleMapsKey,
            'googleMapsEnabled' => filled($googleMapsKey),
            'editingVehiclePrice' => $vehicle ? \App\Support\VehiclePricePresenter::present((float) $vehicle->price, $vehicle->currency, $base['exchangeQuote']) : null,
        ];
    }

    private function mediaData(Request $request): array
    {
        $base = $this->baseData($request);
        $vehicles = $base['vehicles']->values();
        $selectedVehicleId = $request->integer('vehicle') ?: null;
        $selectedVehicle = $vehicles->firstWhere('id', $selectedVehicleId);

        if (! $selectedVehicle) {
            $selectedVehicle = $vehicles->first();
        }

        return $base + [
            'vehiclesWithMedia' => $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->media->isNotEmpty())->values(),
            'mediaVehicles' => $vehicles,
            'selectedMediaVehicle' => $selectedVehicle,
            'photoSlots' => config('vehicle.photo_slots', []),
        ];
    }

    private function billingData(Request $request): array
    {
        $base = $this->baseData($request);
        $selectedPlan = $base['plans']->firstWhere('slug', $request->string('plan')->toString())
            ?? $base['scheduledPlan']
            ?? $base['subscription']?->plan
            ?? $base['plans']->first();

        return $base + [
            'paymentMethods' => $this->paymentMethods(),
            'selectedPlan' => $selectedPlan,
        ];
    }

    private function baseData(Request $request): array
    {
        $user = $request->user();
        $vehicles = Vehicle::query()
            ->with(['make', 'model', 'media'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $capabilities = $this->limitGuard->capabilities($user);
        $plan = $capabilities['plan'];
        $plans = Plan::query()->where('is_active', true)->orderBy('price')->get();
        $subscription = Subscription::query()->with('plan')->where('user_id', $user->id)->where('status', 'active')->where(function ($query): void {
            $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        })->latest('starts_at')->latest('id')->first();
        $scheduledSubscription = Subscription::query()->with('plan')->where('user_id', $user->id)->where('status', 'pending')->where(function ($query): void {
            $query->whereNull('starts_at')->orWhere('starts_at', '>=', now());
        })->orderBy('starts_at')->orderBy('id')->first();
        $transactions = Transaction::query()->with('plan')->where('user_id', $user->id)->latest()->take(16)->get();
        $makes = VehicleMake::query()->active()->with(['models' => fn ($query) => $query->active()->orderBy('name')])->orderBy('name')->get();
        $categories = LifestyleCategory::query()->orderBy('name')->get();
        $activeListings = $vehicles->whereIn('status', ['published', 'paused'])->count();
        $leadCount = $vehicles->sum('lead_count');
        $processingCount = $vehicles->flatMap->media->where('processing_status', 'pending')->count();
        $expiredListings = $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->expires_at && $vehicle->expires_at->isPast());
        $publishedCount = $vehicles->where('status', 'published')->count();
        $viewCount = $vehicles->sum('view_count');
        $conversationCount = Conversation::query()
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->whereHas('vehicle', fn ($query) => $query->where('user_id', $user->id))
            ->count();

        return [
            'vehicles' => $vehicles,
            'capabilities' => $capabilities,
            'currentPlan' => $plan,
            'plans' => $plans,
            'subscription' => $subscription,
            'scheduledSubscription' => $scheduledSubscription,
            'scheduledPlan' => $scheduledSubscription?->plan,
            'transactions' => $transactions,
            'paypalConfigured' => filled(config('paypal.client_id')) && filled(config('paypal.client_secret')),
            'makes' => $makes,
            'categories' => $categories,
            'activeListingsCount' => $activeListings,
            'leadCount' => $leadCount,
            'viewCount' => $viewCount,
            'conversationCount' => $conversationCount,
            'processingCount' => $processingCount,
            'draftCount' => $vehicles->where('status', 'draft')->count(),
            'publishedCount' => $publishedCount,
            'pausedCount' => $vehicles->where('status', 'paused')->count(),
            'expiredListings' => $expiredListings,
            'freeRenewableCount' => $expiredListings->filter(fn (Vehicle $vehicle) => $this->lifecycleService->canRefreshPublication($user, $vehicle))->count(),
            'exchangeQuote' => $this->exchangeRateService->latest(),
        ];
    }

    private function payload(UpsertVehicleRequest $request): array
    {
        return collect($request->validated())
            ->except(['images', 'optional_images', 'required_images', 'lifestyle_category_ids'])
            ->merge([
                'currency' => strtoupper((string) $request->input('currency', 'CRC')),
                'country_code' => strtoupper((string) $request->input('country_code', 'CR')),
                'mileage_unit' => $request->input('mileage_unit', 'km'),
                'status' => $request->input('status', 'draft'),
                'publication_tier' => $request->input('publication_tier', 'basic'),
                'features' => array_values(array_filter($request->input('features', []))),
                'metadata' => $request->input('metadata', []),
                'supports_360' => $request->boolean('supports_360'),
                'has_video' => $request->boolean('has_video'),
                'is_verified_plate' => $request->boolean('is_verified_plate'),
            ])
            ->toArray();
    }

    private function syncLifestyleCategories(Vehicle $vehicle, array $categoryIds): void
    {
        $vehicle->lifestyleCategories()->sync(collect($categoryIds)->mapWithKeys(fn ($id) => [$id => ['score' => 100]]));
    }

    private function uniqueSlug(string $title, int $year, ?int $ignoreId = null): string
    {
        $base = Str::slug(trim($title).' '.$year) ?: 'vehiculo';
        $slug = $base;
        $counter = 2;

        while (Vehicle::query()->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    private function authorizeVehicleAccess(Request $request, Vehicle $vehicle): void
    {
        $user = $request->user();
        if ($user->hasRole('admin') || $vehicle->user_id === $user->id) {
            return;
        }

        abort(403, 'No puedes gestionar est? publicacion.');
    }

    private function assertImageLimit($user, ?Vehicle $vehicle, int $newImagesCount): void
    {
        $currentImages = $vehicle?->media()->count() ?? 0;
        $this->limitGuard->ensureCanUploadImages($user, $currentImages + $newImagesCount);
    }

    private function incomingImageCount(Request $request): int
    {
        return count($request->file('images', []))
            + count($request->file('optional_images', []))
            + count(array_filter($request->file('required_images', [])));
    }

    private function queueMediaFromRequest(Request $request, Vehicle $vehicle, bool $replaceSlots): array
    {
        $queuedMediaIds = [];

        foreach ($request->file('required_images', []) as $slot => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            if ($replaceSlots) {
                $vehicle->media()->where('alt_text', $slot)->get()->each(fn (VehicleMedia $media) => $this->imageProcessor->delete($media));
            }

            $queuedMediaIds[] = $this->stageLabeledMedia($vehicle, $file, $slot);
        }

        foreach ($request->file('optional_images', []) as $file) {
            if ($file instanceof UploadedFile) {
                $queuedMediaIds[] = $this->stageLabeledMedia($vehicle, $file, 'extra');
            }
        }

        foreach ($request->file('images', []) as $file) {
            if ($file instanceof UploadedFile) {
                $queuedMediaIds[] = $this->stageLabeledMedia($vehicle, $file, 'extra');
            }
        }

        return array_values(array_filter($queuedMediaIds));
    }

    private function stageLabeledMedia(Vehicle $vehicle, UploadedFile $file, string $label): int
    {
        $media = $this->imageProcessor->stage($vehicle, $file, ! $vehicle->media()->where('is_primary', true)->exists());
        $media->forceFill(['alt_text' => $label])->save();

        return $media->id;
    }

    private function paymentMethods(): array
    {
        $defaults = [
            'offline' => [
                'cash' => ['label' => 'Efectivo', 'enabled' => true],
                'bank_transfer' => ['label' => 'Transferencia', 'enabled' => true],
                'sinpe_movil' => ['label' => 'Sinpe Movil', 'enabled' => true],
            ],
            'online' => [
                'paypal' => ['label' => 'PayPal', 'enabled' => true],
                'tilopay' => ['label' => 'Tilopay', 'enabled' => false],
            ],
        ];

        return $this->settingsService->get('billing.payment_methods', $defaults);
    }
}







