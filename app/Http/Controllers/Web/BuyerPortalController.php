<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Comparison;
use App\Models\Conversation;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BuyerPortalController extends Controller
{
    public function index(Request $request)
    {
        return view('portal.buyer.overview', $this->overviewData($request));
    }

    public function favorites(Request $request)
    {
        $data = $this->baseData($request);

        return view('portal.buyer.favorites', $data + [
            'favoritesList' => $request->user()->favorites()
                ->whereHas('vehicle', fn ($query) => $this->scopeVisibleVehicles($query))
                ->with(['vehicle.make', 'vehicle.model', 'vehicle.media'])
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function comparisons(Request $request)
    {
        return view('portal.buyer.comparisons', $this->baseData($request));
    }

    public function searches(Request $request)
    {
        $data = $this->baseData($request);

        return view('portal.buyer.searches', $data + [
            'savedSearchList' => $request->user()->savedSearches()->latest()->paginate(10)->withQueryString(),
        ]);
    }

    public function messages(Request $request)
    {
        $data = $this->baseData($request);

        return view('portal.buyer.messages', $data + [
            'conversationList' => Conversation::query()
                ->with(['vehicle'])
                ->whereHas('participants', fn ($query) => $query->where('users.id', $request->user()->id))
                ->latest('last_message_at')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    private function overviewData(Request $request): array
    {
        $data = $this->baseData($request);
        $activity = [
            ['label' => 'Favoritos', 'value' => $data['savedCount']],
            ['label' => 'Comparaciones', 'value' => $data['compareCount']],
            ['label' => 'Búsquedas', 'value' => $data['searchCount']],
            ['label' => 'Mensajes', 'value' => $data['conversationCount']],
        ];

        $max = max(1, collect($activity)->max('value'));

        return $data + [
            'buyerActivityChart' => collect($activity)->map(fn ($item) => $item + [
                'width' => (int) round(($item['value'] / $max) * 100),
            ]),
        ];
    }

    private function baseData(Request $request): array
    {
        $user = $request->user();

        $favorites = $user->favorites()
            ->whereHas('vehicle', fn ($query) => $this->scopeVisibleVehicles($query))
            ->with(['vehicle.make', 'vehicle.model', 'vehicle.media'])
            ->latest()
            ->take(12)
            ->get();

        $comparison = Comparison::query()
            ->where('user_id', $user->id)
            ->with(['vehicles' => fn ($query) => $this->scopeVisibleVehicles($query)->with(['make', 'model', 'media'])])
            ->latest()
            ->first();
        $savedSearches = $user->savedSearches()->latest()->take(12)->get();
        $conversations = Conversation::query()
            ->with(['vehicle'])
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->latest('last_message_at')
            ->take(12)
            ->get();
        $vehicles = Vehicle::query()
            ->with(['make', 'model', 'media'])
            ->where(function ($query): void {
                $this->scopeVisibleVehicles($query);
            })
            ->latest()
            ->take(6)
            ->get();
        $comparisonVehicles = $comparison?->vehicles ?? collect();

        return [
            'vehicles' => $vehicles,
            'favorites' => $favorites,
            'savedSearches' => $savedSearches,
            'comparisonVehicles' => $comparisonVehicles,
            'comparisonRecommendation' => $this->comparisonRecommendation($comparisonVehicles),
            'conversations' => $conversations,
            'savedCount' => $favorites->count(),
            'matchCount' => $vehicles->count(),
            'compareCount' => $comparisonVehicles->count(),
            'searchCount' => $savedSearches->count(),
            'conversationCount' => $conversations->count(),
        ];
    }

    private function scopeVisibleVehicles($query)
    {
        return $query
            ->where('status', 'published')
            ->where(function ($builder): void {
                $builder->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }

    private function comparisonRecommendation(Collection $vehicles): ?array
    {
        if ($vehicles->count() < 2) {
            return null;
        }

        $priceMin = (float) $vehicles->min('price');
        $priceMax = (float) $vehicles->max('price');
        $yearMin = (int) $vehicles->min('year');
        $yearMax = (int) $vehicles->max('year');
        $mileageMin = (float) $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->mileage !== null)->min('mileage');
        $mileageMax = (float) $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->mileage !== null)->max('mileage');
        $averagePrice = (float) $vehicles->avg('price');
        $averageMileage = (float) $vehicles->filter(fn (Vehicle $vehicle) => $vehicle->mileage !== null)->avg('mileage');

        $ranked = $vehicles->map(function (Vehicle $vehicle) use ($priceMin, $priceMax, $yearMin, $yearMax, $mileageMin, $mileageMax, $averagePrice, $averageMileage): array {
            $priceScore = $this->inverseScore((float) $vehicle->price, $priceMin, $priceMax);
            $yearScore = $this->directScore((float) ($vehicle->year ?? $yearMin), $yearMin, $yearMax);
            $mileageValue = $vehicle->mileage !== null ? (float) $vehicle->mileage : $mileageMax;
            $mileageScore = $this->inverseScore($mileageValue, $mileageMin ?: 0, $mileageMax ?: max(1, $mileageValue));
            $bonusScore = in_array($vehicle->fuel_type, ['Híbrido', 'El?ctrico', 'PHEV'], true) ? 8 : 0;
            $leadScore = min(7, (int) $vehicle->lead_count);
            $score = (int) round(($priceScore * 0.38) + ($yearScore * 0.28) + ($mileageScore * 0.24) + $bonusScore + $leadScore);
            $reasons = [];

            if ((float) $vehicle->price === $priceMin) {
                $reasons[] = 'es la opción más económica del grupo';
            } elseif ((float) $vehicle->price <= $averagePrice) {
                $reasons[] = 'se mantiene por debajo del precio promedio del comparador';
            }

            if ((int) $vehicle->year === $yearMax) {
                $reasons[] = 'tiene uno de los años más recientes';
            }

            if ($vehicle->mileage !== null) {
                if ((float) $vehicle->mileage === $mileageMin) {
                    $reasons[] = 'tiene el kilometraje más bajo';
                } elseif ($averageMileage > 0 && (float) $vehicle->mileage <= $averageMileage) {
                    $reasons[] = 'su kilometraje está por debajo del promedio';
                }
            }

            if (in_array($vehicle->fuel_type, ['Híbrido', 'El?ctrico', 'PHEV'], true)) {
                $reasons[] = 'ofrece una motorización más eficiente';
            }

            if ($vehicle->lead_count >= 3) {
                $reasons[] = 'ya despierta interés real entre otros compradores';
            }

            if ($reasons === []) {
                $reasons[] = 'mantiene un balance sano entre precio, año y kilometraje';
            }

            return [
                'vehicle' => $vehicle,
                'score' => max(1, min(100, $score)),
                'reasons' => array_values(array_unique(array_slice($reasons, 0, 3))),
                'headline' => $this->recommendationHeadline($vehicle, $priceMin, $yearMax, $mileageMin),
            ];
        })->sortByDesc('score')->values();

        $winner = $ranked->first();
        $runnerUp = $ranked->skip(1)->first();

        return [
            'winner' => $winner,
            'runnerUp' => $runnerUp,
            'ranking' => $ranked,
        ];
    }

    private function directScore(float $value, float $min, float $max): float
    {
        if ($max <= $min) {
            return 100;
        }

        return (($value - $min) / ($max - $min)) * 100;
    }

    private function inverseScore(float $value, float $min, float $max): float
    {
        if ($max <= $min) {
            return 100;
        }

        return (1 - (($value - $min) / ($max - $min))) * 100;
    }

    private function recommendationHeadline(Vehicle $vehicle, float $priceMin, int $yearMax, float $mileageMin): string
    {
        if ((float) $vehicle->price === $priceMin) {
            return 'Destaca por precio';
        }

        if ((int) $vehicle->year === $yearMax) {
            return 'Dest?ca por año';
        }

        if ($vehicle->mileage !== null && (float) $vehicle->mileage === $mileageMin) {
            return 'Destaca por kilometraje';
        }

        return 'La opción más equilibrada';
    }
}
