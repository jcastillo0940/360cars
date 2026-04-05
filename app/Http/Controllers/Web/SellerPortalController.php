<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\UploadVehicleMediaRequest;
use App\Http\Requests\Vehicle\UpsertVehicleRequest;
use App\Models\LifestyleCategory;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Models\VehicleMedia;
use App\Services\Currency\ExchangeRateService;
use App\Services\Media\VehicleImageProcessor;
use App\Services\Publication\PublicationLifecycleService;
use App\Services\Publication\PublicationLimitGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SellerPortalController extends Controller
{
    public function __construct(
        private readonly VehicleImageProcessor $imageProcessor,
        private readonly PublicationLimitGuard $limitGuard,
        private readonly PublicationLifecycleService $lifecycleService,
        private readonly ExchangeRateService $exchangeRateService,
    ) {
    }

    public function index(Request $request)
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
        $subscription = Subscription::query()->with('plan')->where('user_id', $user->id)->latest('id')->first();
        $transactions = Transaction::query()->with('plan')->where('user_id', $user->id)->latest()->take(8)->get();
        $makes = VehicleMake::query()->with('models')->orderBy('name')->get();
        $categories = LifestyleCategory::query()->orderBy('name')->get();
        $activeListings = $vehicles->whereIn('status', ['published', 'paused'])->count();
        $leadCount = $vehicles->sum('lead_count');
        $processingCount = $vehicles->flatMap->media->where('processing_status', 'pending')->count();
        $expiredListings = $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->expires_at && $vehicle->expires_at->isPast());

        return view('portal.seller', [
            'vehicles' => $vehicles,
            'capabilities' => $capabilities,
            'currentPlan' => $plan,
            'plans' => $plans,
            'subscription' => $subscription,
            'transactions' => $transactions,
            'paypalConfigured' => filled(config('paypal.client_id')) && filled(config('paypal.client_secret')),
            'makes' => $makes,
            'categories' => $categories,
            'activeListingsCount' => $activeListings,
            'leadCount' => $leadCount,
            'processingCount' => $processingCount,
            'draftCount' => $vehicles->where('status', 'draft')->count(),
            'publishedCount' => $vehicles->where('status', 'published')->count(),
            'pausedCount' => $vehicles->where('status', 'paused')->count(),
            'expiredListings' => $expiredListings,
            'freeRenewableCount' => $expiredListings->filter(fn (Vehicle $vehicle) => $this->lifecycleService->canRefreshPublication($user, $vehicle))->count(),
            'exchangeQuote' => $this->exchangeRateService->latest(),
        ]);
    }

    public function store(UpsertVehicleRequest $request): RedirectResponse
    {
        $user = $request->user();
        $publicationTier = $request->input('publication_tier', 'basic');
        $this->limitGuard->ensureCanUseTier($user, $publicationTier);
        $this->assertImageLimit($user, null, count($request->file('images', [])));

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

            if ($request->hasFile('images')) {
                $queuedMediaIds = $this->imageProcessor->stageMany($vehicle, $request->file('images'));
            }

            return $vehicle;
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return redirect()->route('seller.dashboard')->with('status', "Publicacion {$vehicle->title} creada correctamente.");
    }

    public function update(UpsertVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $user = $request->user();
        $tier = $request->input('publication_tier', $vehicle->publication_tier);
        $this->limitGuard->ensureCanUseTier($user, $tier);
        $this->assertImageLimit($user, $vehicle, count($request->file('images', [])));

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

            $this->lifecycleService->applyPlanBenefits($user, $vehicle, $targetStatus);

            if ($request->hasFile('images')) {
                $queuedMediaIds = $this->imageProcessor->stageMany($vehicle, $request->file('images'));
            }
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return redirect()->route('seller.dashboard')->with('status', 'Publicacion actualizada correctamente.');
    }

    public function publish(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->limitGuard->ensureCanUseTier($request->user(), $vehicle->publication_tier);
        $this->limitGuard->ensureCanPublish($request->user(), $vehicle);

        $vehicle->update(['status' => 'published', 'published_at' => $vehicle->published_at ?? now()]);
        $this->lifecycleService->applyPlanBenefits($request->user(), $vehicle, 'published');

        return back()->with('status', 'Publicacion publicada correctamente.');
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
            $vehicle->delete();
        });

        return back()->with('status', 'Publicacion eliminada correctamente.');
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

    private function payload(UpsertVehicleRequest $request): array
    {
        return collect($request->validated())
            ->except(['images', 'lifestyle_category_ids'])
            ->merge([
                'currency' => strtoupper((string) $request->input('currency', 'CRC')),
                'country_code' => strtoupper((string) $request->input('country_code', 'CR')),
                'mileage_unit' => $request->input('mileage_unit', 'km'),
                'status' => $request->input('status', 'draft'),
                'publication_tier' => $request->input('publication_tier', 'basic'),
                'features' => array_values(array_filter(array_map('trim', explode(',', (string) $request->input('features_list', ''))))) ?: $request->input('features', []),
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

        abort(403, 'No puedes gestionar esta publicacion.');
    }

    private function assertImageLimit($user, ?Vehicle $vehicle, int $newImagesCount): void
    {
        $currentImages = $vehicle?->media()->count() ?? 0;
        $this->limitGuard->ensureCanUploadImages($user, $currentImages + $newImagesCount);
    }
}
