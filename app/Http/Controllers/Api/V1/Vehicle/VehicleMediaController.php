<?php

namespace App\Http\Controllers\Api\V1\Vehicle;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\ReorderVehicleMediaRequest;
use App\Http\Requests\Vehicle\UploadVehicleMediaRequest;
use App\Http\Resources\Vehicle\VehicleMediaResource;
use App\Models\Vehicle;
use App\Models\VehicleMedia;
use App\Services\Media\VehicleImageProcessor;
use App\Services\Publication\PublicationLimitGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleMediaController extends Controller
{
    public function __construct(
        private readonly VehicleImageProcessor $imageProcessor,
        private readonly PublicationLimitGuard $limitGuard,
    ) {
    }

    public function store(UploadVehicleMediaRequest $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->limitGuard->ensureCanUploadImages($request->user(), $vehicle->media()->count() + count($request->file('images', [])));

        $queuedMediaIds = $this->imageProcessor->stageMany($vehicle, $request->file('images'));
        $this->imageProcessor->dispatchMany($queuedMediaIds);

        return response()->json([
            'message' => 'Imagenes agregadas y encoladas correctamente.',
            'data' => VehicleMediaResource::collection($vehicle->fresh()->media()->orderBy('sort_order')->get()),
        ], 201);
    }

    public function reorder(ReorderVehicleMediaRequest $request, Vehicle $vehicle): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $media = $vehicle->media()->orderBy('sort_order')->get();
        $requestedIds = $request->input('media_ids', []);

        abort_unless(count($requestedIds) === $media->count(), 422, 'Debes enviar todos los ids de la galer?a.');
        abort_unless($media->pluck('id')->sort()->values()->all() === collect($requestedIds)->sort()->values()->all(), 422, 'La galer?a enviada no coincide con la publicacion.');

        foreach ($requestedIds as $index => $mediaId) {
            $vehicle->media()->whereKey($mediaId)->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'message' => 'Galeria reordenada correctamente.',
            'data' => VehicleMediaResource::collection($vehicle->fresh()->media()->orderBy('sort_order')->get()),
        ]);
    }

    public function makePrimary(Request $request, Vehicle $vehicle, VehicleMedia $media): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->ensureVehicleOwnsMedia($vehicle, $media);
        $media = $this->imageProcessor->makePrimary($vehicle, $media);

        return response()->json([
            'message' => 'Imagen principal actualizada.',
            'data' => new VehicleMediaResource($media),
        ]);
    }

    public function destroy(Request $request, Vehicle $vehicle, VehicleMedia $media): JsonResponse
    {
        $this->authorizeVehicleAccess($request, $vehicle);
        $this->ensureVehicleOwnsMedia($vehicle, $media);
        $this->imageProcessor->delete($media);

        return response()->json(['message' => 'Imagen eliminada correctamente.']);
    }

    private function authorizeVehicleAccess(Request $request, Vehicle $vehicle): void
    {
        $user = $request->user();
        if ($user->hasRole('admin') || $vehicle->user_id === $user->id) {
            return;
        }
        abort(403, 'No puedes gestionar est? publicacion.');
    }

    private function ensureVehicleOwnsMedia(Vehicle $vehicle, VehicleMedia $media): void
    {
        abort_unless($media->vehicle_id === $vehicle->id, 404);
    }
}
