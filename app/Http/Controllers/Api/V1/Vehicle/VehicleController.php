<?php

namespace App\Http\Controllers\Api\V1\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\UpsertVehicleRequest;
use App\Http\Resources\Vehicle\VehicleResource;
use App\Models\Vehicle;
use App\Services\Media\VehicleImageProcessor;
use App\Services\Publication\PublicationLifecycleService;
use App\Services\Publication\PublicationLimitGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VehicleController extends Controller
{
    public function __construct(
        private readonly VehicleImageProcessor $imageProcessor,
        private readonly PublicationLimitGuard $limitGuard,
        private readonly PublicationLifecycleService $lifecycleService,
    ) {
    }

    public function index(Request $request)
    {
        $query = Vehicle::query()->with(['owner', 'make', 'model', 'media', 'lifestyleCategories'])->where('status', 'published')->latest();

        if ($search = $request->string('q')->toString()) {
            $query->where(function ($builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }

        foreach (['vehicle_make_id', 'vehicle_model_id', 'condition', 'body_type', 'fuel_type', 'transmission', 'year'] as $filter) {
            if ($value = $request->input($filter)) {
                $query->where($filter, $value);
            }
        }

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->input('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->input('max_price'));
        }

        return VehicleResource::collection($query->paginate($request->integer('per_page', 12)));
    }

    public function myIndex(Request $request)
    {
        $query = Vehicle::query()->with(['owner', 'make', 'model', 'media', 'lifestyleCategories'])->latest();
        $user = $request->user();

        if (! $user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return VehicleResource::collection($query->paginate($request->integer('per_page', 15)));
    }

    public function capabilities(Request $request): JsonResponse
    {
        $capabilities = $this->limitGuard->capabilities($request->user());
        $plan = $capabilities['plan'];

        return response()->json([
            'plan' => [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'price' => $plan->price,
                'currency' => $plan->currency,
                'photo_limit' => $plan->photo_limit,
                'max_active_listings' => $plan->max_active_listings,
                'allows_video' => $plan->allows_video,
                'allows_360' => $plan->allows_360,
                'supports_credit_leads' => $plan->supports_credit_leads,
                'supports_trade_in' => $plan->supports_trade_in,
            ],
            'queue' => [
                'connection' => config('media.queue_connection'),
                'name' => config('media.queue_name'),
                'staging_disk' => config('media.staging_disk'),
                'vehicle_disk' => config('media.vehicle_disk'),
            ],
            'allowed_publication_tiers' => $capabilities['allowed_tiers'],
            'active_listings' => $capabilities['active_listings'],
            'remaining_active_listings' => $capabilities['remaining_active_listings'],
        ]);
    }

    public function show(Vehicle $vehicle): VehicleResource
    {
        $vehicle->load(['owner', 'make', 'model', 'media', 'lifestyleCategories']);
        abort_if($vehicle->status !== 'published', 404);
        return new VehicleResource($vehicle);
    }

    public function myShow(Request $request, Vehicle $vehicle): VehicleResource
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $vehicle->load(['owner', 'make', 'model', 'media', 'lifestyleCategories']);
        return new VehicleResource($vehicle);
    }

    public function store(UpsertVehicleRequest $request): JsonResponse
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

            return $vehicle->load(['owner', 'make', 'model', 'media', 'lifestyleCategories']);
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return response()->json([
            'message' => 'Publicacion creada correctamente. El procesamiento de imagenes fue encolado.',
            'data' => new VehicleResource($vehicle->fresh()->load(['owner', 'make', 'model', 'media', 'lifestyleCategories'])),
        ], 201);
    }

    public function update(UpsertVehicleRequest $request, Vehicle $vehicle): JsonResponse
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

        $vehicle = DB::transaction(function () use ($request, $vehicle, $user, &$queuedMediaIds, $targetStatus) {
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

            $vehicle = $this->lifecycleService->applyPlanBenefits($user, $vehicle, $targetStatus);

            if ($request->hasFile('images')) {
                $queuedMediaIds = $this->imageProcessor->stageMany($vehicle, $request->file('images'));
            }

            return $vehicle->load(['owner', 'make', 'model', 'media', 'lifestyleCategories']);
        });

        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return response()->json([
            'message' => 'Publicacion actualizada correctamente. Las nuevas imagenes fueron encoladas para procesamiento.',
            'data' => new VehicleResource($vehicle->fresh()->load(['owner', 'make', 'model', 'media', 'lifestyleCategories'])),
        ]);
    }

    public function publish(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->limitGuard->ensureCanUseTier($request->user(), $vehicle->publication_tier);
        $this->limitGuard->ensureCanPublish($request->user(), $vehicle);

        $vehicle->update(['status' => 'published', 'published_at' => $vehicle->published_at ?? now()]);
        $vehicle = $this->lifecycleService->applyPlanBenefits($request->user(), $vehicle, 'published');

        return response()->json([
            'message' => 'Publicacion publicada correctamente.',
            'data' => new VehicleResource($vehicle->fresh()->load(['owner', 'make', 'model', 'media', 'lifestyleCategories'])),
        ]);
    }

    public function pause(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $vehicle->update(['status' => 'paused']);

        return response()->json([
            'message' => 'Publicacion pausada correctamente.',
            'data' => new VehicleResource($vehicle->fresh()->load(['owner', 'make', 'model', 'media', 'lifestyleCategories'])),
        ]);
    }

    public function destroy(Request $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $vehicle->load('media');

        DB::transaction(function () use ($vehicle): void {
            $this->imageProcessor->deleteAllForVehicle($vehicle);
            $vehicle->delete();
        });

        return response()->json(['message' => 'Publicacion eliminada correctamente.']);
    }

    private function payload(UpsertVehicleRequest $request): array
    {
        return collect($request->validated())
            ->except(['images', 'lifestyle_category_ids'])
            ->merge([
                'currency' => strtoupper((string) $request->input('currency', 'USD')),
                'country_code' => strtoupper((string) $request->input('country_code', 'CR')),
                'mileage_unit' => $request->input('mileage_unit', 'km'),
                'status' => $request->input('status', 'draft'),
                'publication_tier' => $request->input('publication_tier', 'basic'),
                'features' => $request->input('features', []),
                'metadata' => $request->input('metadata', []),
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
}
