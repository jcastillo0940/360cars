<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class BuyerPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->with(['vehicle.make', 'vehicle.model', 'vehicle.media'])
            ->latest()
            ->take(6)
            ->get();

        $comparison = $user->comparisons()->with(['vehicles.make', 'vehicles.model'])->latest()->first();
        $savedSearches = $user->savedSearches()->latest()->take(6)->get();
        $conversations = Conversation::query()
            ->with(['vehicle'])
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->latest('last_message_at')
            ->take(6)
            ->get();
        $vehicles = Vehicle::query()->with(['make', 'model', 'media'])->where('status', 'published')->latest()->take(6)->get();

        return view('portal.buyer', [
            'vehicles' => $vehicles,
            'favorites' => $favorites,
            'savedSearches' => $savedSearches,
            'comparisonVehicles' => $comparison?->vehicles ?? collect(),
            'conversations' => $conversations,
            'savedCount' => $favorites->count(),
            'matchCount' => $vehicles->count(),
            'compareCount' => $comparison?->vehicles->count() ?? 0,
        ]);
    }
}
