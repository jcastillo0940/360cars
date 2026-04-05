<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\SavedSearch;
use App\Models\Vehicle;
use App\Models\VehicleFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BuyerEngagementController extends Controller
{
    public function favorite(Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        abort_unless($vehicle->status === 'published', 404);

        $favorite = VehicleFavorite::firstOrCreate([
            'user_id' => auth()->id(),
            'vehicle_id' => $vehicle->id,
        ]);

        return request()->expectsJson()
            ? response()->json(['favorited' => true, 'favorite_id' => $favorite->id])
            : back();
    }

    public function unfavorite(Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        VehicleFavorite::query()
            ->where('user_id', auth()->id())
            ->where('vehicle_id', $vehicle->id)
            ->delete();

        return request()->expectsJson()
            ? response()->json(['favorited' => false])
            : back();
    }

    public function addToComparison(Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        abort_unless($vehicle->status === 'published', 404);

        $comparison = Comparison::firstOrCreate([
            'user_id' => auth()->id(),
            'name' => 'Mi comparador',
        ]);

        if ($comparison->vehicles()->count() >= 4 && ! $comparison->vehicles()->whereKey($vehicle->id)->exists()) {
            return response()->json([
                'message' => 'El comparador admite hasta 4 vehiculos.',
            ], 422);
        }

        $comparison->vehicles()->syncWithoutDetaching([$vehicle->id]);

        return request()->expectsJson()
            ? response()->json([
                'compared' => true,
                'comparison_count' => $comparison->vehicles()->count(),
            ])
            : back();
    }

    public function removeFromComparison(Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        $comparison = Comparison::query()->where('user_id', auth()->id())->first();

        if ($comparison) {
            $comparison->vehicles()->detach($vehicle->id);
        }

        return request()->expectsJson()
            ? response()->json([
                'compared' => false,
                'comparison_count' => $comparison?->vehicles()->count() ?? 0,
            ])
            : back();
    }

    public function saveSearch(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'notification_frequency' => ['nullable', Rule::in(['instant', 'daily', 'weekly'])],
            'filters' => ['required', 'array'],
        ]);

        $search = SavedSearch::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'filters' => $validated['filters'],
            'notification_frequency' => $validated['notification_frequency'] ?? 'instant',
            'is_active' => true,
        ]);

        return request()->expectsJson()
            ? response()->json(['saved' => true, 'saved_search_id' => $search->id])
            : back();
    }

    public function destroySavedSearch(SavedSearch $savedSearch): RedirectResponse
    {
        abort_unless($savedSearch->user_id === auth()->id(), 403);

        $savedSearch->delete();

        return back();
    }

    public function contactSeller(Request $request, Vehicle $vehicle): JsonResponse|RedirectResponse
    {
        abort_unless($vehicle->status === 'published', 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $conversation = Conversation::query()
            ->where('vehicle_id', $vehicle->id)
            ->whereHas('participants', fn ($query) => $query->where('users.id', auth()->id()))
            ->whereHas('participants', fn ($query) => $query->where('users.id', $vehicle->user_id))
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'vehicle_id' => $vehicle->id,
                'subject' => 'Consulta por '.$vehicle->title,
                'status' => 'open',
                'last_message_at' => now(),
            ]);

            $conversation->participants()->attach(auth()->id(), [
                'role' => 'buyer',
                'last_read_at' => now(),
            ]);

            $conversation->participants()->attach($vehicle->user_id, [
                'role' => 'seller',
                'last_read_at' => null,
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => auth()->id(),
            'body' => $validated['body'],
        ]);

        $conversation->forceFill([
            'last_message_at' => now(),
        ])->save();

        return request()->expectsJson()
            ? response()->json(['sent' => true, 'conversation_id' => $conversation->id])
            : back();
    }
}
