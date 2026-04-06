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
            ['label' => 'Busquedas', 'value' => $data['searchCount']],
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
            ->with(['vehicle.make', 'vehicle.model', 'vehicle.media'])
            ->latest()
            ->take(12)
            ->get();

        $comparison = Comparison::query()->where('user_id', $user->id)->with(['vehicles.make', 'vehicles.model', 'vehicles.media'])->latest()->first();
        $savedSearches = $user->savedSearches()->latest()->take(12)->get();
        $conversations = Conversation::query()
            ->with(['vehicle'])
            ->whereHas('participants', fn ($query) => $query->where('users.id', $user->id))
            ->latest('last_message_at')
            ->take(12)
            ->get();
        $vehicles = Vehicle::query()->with(['make', 'model', 'media'])->where('status', 'published')->latest()->take(6)->get();
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
            $bonusScore = in_array($vehicle->fuel_type, ['Hibrido', 'Electrico', 'PHEV'], true) ? 8 : 0;
            $leadScore = min(7, (int) $vehicle->lead_count);
            $score = (int) round(($priceScore * 0.38) + ($yearScore * 0.28) + ($mileageScore * 0.24) + $bonusScore + $leadScore);
            $reasons = [];

            if ((float) $vehicle->price === $priceMin) {
                $reasons[] = 'es la opcion mas economica del grupo';
            } elseif ((float) $vehicle->price <= $averagePrice) {
                $reasons[] = 'se mantiene por debajo del precio promedio del comparador';
            }

            if ((int) $vehicle->year === $yearMax) {
                $reasons[] = 'tiene uno de los anos mas recientes';
            }

            if ($vehicle->mileage !== null) {
                if ((float) $vehicle->mileage === $mileageMin) {
                    $reasons[] = 'tiene el kilometraje mas bajo';
                } elseif ($averageMileage > 0 && (float) $vehicle->mileage <= $averageMileage) {
                    $reasons[] = 'su kilometraje esta por debajo del promedio';
                }
            }

            if (in_array($vehicle->fuel_type, ['Hibrido', 'Electrico', 'PHEV'], true)) {
                $reasons[] = 'ofrece una motorizacion mas eficiente';
            }

            if ($vehicle->lead_count >= 3) {
                $reasons[] = 'ya despierta interes real entre otros compradores';
            }

            if ($reasons === []) {
                $reasons[] = 'mantiene un balance sano entre precio, ano y kilometraje';
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
            return 'Destaca por ano';
        }

        if ($vehicle->mileage !== null && (float) $vehicle->mileage === $mileageMin) {
            return 'Destaca por kilometraje';
        }

        return 'La opcion mas equilibrada';
    }
}
