<?php

namespace App\Services\Valuation;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleMake;
use App\Models\VehicleModel;
use App\Models\VehicleValuation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VehicleValuationService
{
    public function __construct(
        private readonly ValuationSettingsService $settings,
        private readonly VehicleValuationAiNarrator $aiNarrator,
    ) {
    }

    public function evaluate(array $input, ?User $user = null): VehicleValuation
    {
        $make = VehicleMake::query()->findOrFail($input['vehicle_make_id']);
        $model = VehicleModel::query()->findOrFail($input['vehicle_model_id']);
        $comparables = $this->findComparables($input);
        $baseline = $this->baselineValue($input);
        $market = $this->marketValue($input, $comparables);
        $suggestedRaw = $this->blendValues($baseline, (float) $market['weighted_average'], (float) $market['weighted_median'], $comparables->count(), (float) $market['fit_score']);
        $suggested = $this->roundCrc($suggestedRaw);
        $confidence = $this->confidenceScore($comparables->count(), $input, $market);
        $bandVariance = $this->bandVariance($confidence, (float) $market['dispersion']);
        $minPrice = $this->roundCrc($suggested * (1 - $bandVariance));
        $maxPrice = $this->roundCrc($suggested * (1 + $bandVariance));
        $insights = $this->marketInsights($input, $comparables, $baseline, $market, $suggested, $confidence);
        $aiEnabled = $this->settings->valuationAiEnabled();

        $snapshot = [
            'vehicle_make_id' => (int) $make->id,
            'vehicle_make_name' => $make->name,
            'vehicle_model_id' => (int) $model->id,
            'vehicle_model_name' => $model->name,
            'year' => (int) $input['year'],
            'condition' => (string) $input['condition'],
            'body_type' => (string) $input['body_type'],
            'fuel_type' => (string) $input['fuel_type'],
            'transmission' => (string) $input['transmission'],
            'drivetrain' => $input['drivetrain'] ?? null,
            'mileage' => Arr::get($input, 'mileage') !== null ? (int) $input['mileage'] : null,
            'engine_size' => Arr::get($input, 'engine_size') !== null ? (float) $input['engine_size'] : null,
            'city' => (string) ($input['city'] ?? 'Costa Rica'),
            'price_reference' => Arr::get($input, 'price_reference') !== null ? (float) $input['price_reference'] : null,
        ];

        $algorithmPayload = [
            'baseline_crc' => $baseline,
            'weighted_average_crc' => $market['weighted_average'],
            'weighted_median_crc' => $market['weighted_median'],
            'fit_score' => $market['fit_score'],
            'dispersion' => $market['dispersion'],
            'comparables_count' => $comparables->count(),
            'comparables' => $market['comparables_preview'],
            'band_variance' => $bandVariance,
        ];

        $aiSummary = null;

        if ($aiEnabled && $this->aiNarrator->configured()) {
            $aiSummary = $this->aiNarrator->summarize([
                'snapshot' => $snapshot,
                'suggested_price_crc' => $suggested,
                'price_band_crc' => [$minPrice, $maxPrice],
                'confidence_score' => $confidence,
                'insights' => $insights,
                'algorithm_payload' => $algorithmPayload,
            ]);
        }

        return VehicleValuation::query()->create([
            'vehicle_id' => Arr::get($input, 'vehicle_id'),
            'user_id' => $user?->id,
            'source' => $aiSummary ? 'internal_market_model+ai' : 'internal_market_model',
            'currency' => 'CRC',
            'suggested_price' => $suggested,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'confidence_score' => $confidence,
            'input_snapshot' => $snapshot,
            'share_token' => $this->shareToken(),
            'ai_enabled' => $aiEnabled,
            'ai_summary' => $aiSummary,
            'market_insights' => $insights,
            'algorithm_payload' => $algorithmPayload,
        ]);
    }

    private function findComparables(array $input): Collection
    {
        $baseQuery = Vehicle::query()
            ->where('status', 'published')
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->whereNotNull('price');

        $exact = (clone $baseQuery)
            ->where('vehicle_make_id', $input['vehicle_make_id'])
            ->where('vehicle_model_id', $input['vehicle_model_id'])
            ->whereBetween('year', [max(1950, ((int) $input['year']) - 4), ((int) $input['year']) + 1])
            ->limit(20)
            ->get();

        $pool = $exact;

        if ($pool->count() < 5) {
            $sameMakeBody = (clone $baseQuery)
                ->where('vehicle_make_id', $input['vehicle_make_id'])
                ->where('body_type', $input['body_type'])
                ->whereBetween('year', [max(1950, ((int) $input['year']) - 5), ((int) $input['year']) + 2])
                ->limit(20)
                ->get();

            $pool = $pool->merge($sameMakeBody);
        }

        if ($pool->count() < 7) {
            $sameBody = (clone $baseQuery)
                ->where('body_type', $input['body_type'])
                ->whereBetween('year', [max(1950, ((int) $input['year']) - 3), ((int) $input['year']) + 2])
                ->limit(20)
                ->get();

            $pool = $pool->merge($sameBody);
        }

        return $pool
            ->unique('id')
            ->values()
            ->sortByDesc(fn (Vehicle $vehicle) => $this->fitScore($input, $vehicle))
            ->take(12)
            ->values();
    }

    private function baselineValue(array $input): float
    {
        $bodyType = (string) ($input['body_type'] ?? 'SUV');
        $price = (float) data_get(config('valuation.baseline_prices_crc'), $bodyType, 11000000);
        $price *= (float) data_get(config('valuation.condition_multipliers'), $input['condition'], 1.0);
        $price *= (float) data_get(config('valuation.fuel_multipliers'), $input['fuel_type'], 1.0);
        $price *= (float) data_get(config('valuation.transmission_multipliers'), $input['transmission'], 1.0);
        $price *= (float) data_get(config('valuation.drivetrain_multipliers'), $input['drivetrain'], 1.0);
        $price *= (float) data_get(config('valuation.city_multipliers'), $input['city'], 1.0);

        $age = max(0, now()->year - (int) $input['year']);
        $price *= max(0.28, 1 - ($age * (float) config('valuation.year_adjustment', 0.055)));

        $mileage = (int) ($input['mileage'] ?? 0);
        $annualTarget = max(10000, (int) config('valuation.annual_km_crc', 18000));
        $expectedMileage = $age > 0 ? $annualTarget * $age : $annualTarget;
        $mileageDelta = ($expectedMileage - $mileage) / 10000;
        $price *= max(0.82, 1 + ($mileageDelta * (float) config('valuation.mileage_adjustment_per_10000', 0.012)));

        if (! empty($input['engine_size'])) {
            $price *= 1 + min(0.08, max(-0.04, (((float) $input['engine_size']) - 2.0) * 0.025));
        }

        return max(2500000, $price);
    }

    private function marketValue(array $input, Collection $comparables): array
    {
        if ($comparables->isEmpty()) {
            return [
                'weighted_average' => 0.0,
                'weighted_median' => 0.0,
                'fit_score' => 0.0,
                'dispersion' => 0.18,
                'comparables_preview' => [],
            ];
        }

        $adjusted = $comparables->map(function (Vehicle $vehicle) use ($input): array {
            $price = $this->normalizeComparablePrice($vehicle);
            $yearDelta = ((int) $input['year']) - (int) $vehicle->year;
            $price *= 1 + ($yearDelta * (float) config('valuation.year_adjustment', 0.055));

            $targetMileage = (int) ($input['mileage'] ?? 0);
            $comparableMileage = (int) ($vehicle->mileage ?? 0);
            if ($targetMileage > 0 || $comparableMileage > 0) {
                $mileageDelta = ($comparableMileage - $targetMileage) / 10000;
                $price *= 1 + ($mileageDelta * (float) config('valuation.mileage_adjustment_per_10000', 0.012));
            }

            $fitScore = $this->fitScore($input, $vehicle);

            return [
                'vehicle_id' => $vehicle->id,
                'title' => $vehicle->title,
                'price' => (float) $vehicle->price,
                'adjusted_price' => max(2000000, $price),
                'fit_score' => $fitScore,
                'city' => $vehicle->city,
                'year' => $vehicle->year,
                'match_label' => $this->matchLabel($fitScore),
            ];
        })->sortBy('adjusted_price')->values();

        $trimmed = $this->trimOutliers($adjusted);
        $weightedAverage = $this->weightedAverage($trimmed);
        $weightedMedian = $this->weightedMedian($trimmed);
        $dispersion = $this->dispersion($trimmed, $weightedAverage);

        return [
            'weighted_average' => (float) round($weightedAverage, 2),
            'weighted_median' => (float) round($weightedMedian, 2),
            'fit_score' => (float) round($trimmed->avg('fit_score') ?: 0, 4),
            'dispersion' => (float) round($dispersion, 4),
            'comparables_preview' => $trimmed->sortByDesc('fit_score')->take(6)->values()->all(),
        ];
    }

    private function blendValues(float $baseline, float $weightedAverage, float $weightedMedian, int $comparablesCount, float $fitScore): float
    {
        if ($weightedAverage <= 0 && $weightedMedian <= 0) {
            return $baseline;
        }

        $marketComposite = $weightedAverage > 0 && $weightedMedian > 0
            ? (($weightedAverage * 0.55) + ($weightedMedian * 0.45))
            : max($weightedAverage, $weightedMedian);

        $marketWeight = match (true) {
            $comparablesCount >= 8 => 0.88,
            $comparablesCount >= 5 => 0.80,
            $comparablesCount >= 3 => 0.70,
            default => 0.58,
        };

        $marketWeight *= 0.82 + min(0.18, max(0.0, $fitScore - 0.72));
        $marketWeight = max(0.45, min(0.92, $marketWeight));

        return ($marketComposite * $marketWeight) + ($baseline * (1 - $marketWeight));
    }

    private function confidenceScore(int $comparablesCount, array $input, array $market): float
    {
        $completeness = collect(['mileage', 'engine_size', 'drivetrain', 'city'])
            ->filter(fn (string $field) => filled($input[$field] ?? null))
            ->count();

        $fitBonus = min(10, ((float) ($market['fit_score'] ?? 0)) * 10);
        $dispersionPenalty = min(10, ((float) ($market['dispersion'] ?? 0.18)) * 30);

        $score = (float) config('valuation.min_confidence', 52)
            + min(30, $comparablesCount * 4.5)
            + ($completeness * 2.5)
            + $fitBonus
            - $dispersionPenalty;

        return min((float) config('valuation.max_confidence', 92), max(48, round($score, 2)));
    }

    private function marketInsights(array $input, Collection $comparables, float $baseline, array $market, float $suggested, float $confidence): array
    {
        $insights = [];
        $count = $comparables->count();
        $avgFit = round(((float) ($market['fit_score'] ?? 0)) * 100);

        $insights[] = $count > 0
            ? 'Tomamos '.$count.' comparables locales y los ponderamos por similitud real con tu auto; la coincidencia promedio del set es de '.$avgFit.'%.'
            : 'No encontramos suficientes comparables exactos y reforzamos la evaluaci?n con una linea base por carroceria y depreciación local.';
        $insights[] = 'La depreciación considera año, kilometraje, configuración mecanica y señales de demanda observadas en Costa Rica.';
        $insights[] = 'La recomendacion final combina referencia de mercado y modelo interno con una confianza de '.round($confidence).'%. A mayor dispersion del mercado, m?s amplio se vuelve el rango.';

        if (($input['city'] ?? null) && data_get(config('valuation.city_multipliers'), $input['city'])) {
            $insights[] = 'La ubicacion en '.$input['city'].' introduce un ajuste fino de demanda dentro del rango sugerido.';
        }

        if (($market['weighted_average'] ?? 0) > 0) {
            $difference = $suggested - (float) $market['weighted_average'];
            $direction = $difference >= 0 ? 'por encima' : 'por debajo';
            $insights[] = 'El valor sugerido queda '.abs(round(($difference / max(1, $market['weighted_average'])) * 100)).'% '.$direction.' del promedio ponderado del mercado.';
        } else {
            $insights[] = 'La base interna actual para '.($input['body_type'] ?? 'est? carroceria').' se uso como soporte principal del rango mostrado.';
        }

        if (filled($input['price_reference'] ?? null)) {
            $delta = ((float) $input['price_reference']) - $suggested;
            if (abs($delta) >= 100000) {
                $direction = $delta > 0 ? 'por encima' : 'por debajo';
                $insights[] = 'Tu expectativa inicial est? '.abs(round(($delta / max(1, $suggested)) * 100)).'% '.$direction.' del valor sugerido por el tasador.';
            }
        }

        return $insights;
    }

    private function normalizeComparablePrice(Vehicle $vehicle): float
    {
        $price = (float) $vehicle->price;

        if (strtoupper((string) $vehicle->currency) === 'USD') {
            $price *= (float) config('exchange-rates.test_usd_to_crc', 505.0);
        }

        return $price;
    }

    private function fitScore(array $input, Vehicle $vehicle): float
    {
        $score = 0.0;

        if ((int) $vehicle->vehicle_make_id === (int) $input['vehicle_make_id']) {
            $score += 0.18;
        }

        if ((int) $vehicle->vehicle_model_id === (int) $input['vehicle_model_id']) {
            $score += 0.28;
        }

        if ((string) $vehicle->body_type === (string) $input['body_type']) {
            $score += 0.10;
        }

        if ((string) $vehicle->fuel_type === (string) $input['fuel_type']) {
            $score += 0.08;
        }

        if ((string) $vehicle->transmission === (string) $input['transmission']) {
            $score += 0.08;
        }

        if (filled($input['drivetrain'] ?? null) && (string) $vehicle->drivetrain === (string) $input['drivetrain']) {
            $score += 0.06;
        }

        $yearDistance = abs(((int) $input['year']) - ((int) $vehicle->year));
        $score += max(0, 0.16 - ($yearDistance * 0.035));

        $targetMileage = (int) ($input['mileage'] ?? 0);
        $vehicleMileage = (int) ($vehicle->mileage ?? 0);
        if ($targetMileage > 0 && $vehicleMileage > 0) {
            $mileageDistance = abs($targetMileage - $vehicleMileage);
            $score += max(0, 0.06 - (($mileageDistance / 10000) * 0.008));
        }

        if (($input['city'] ?? null) && (string) $vehicle->city === (string) $input['city']) {
            $score += 0.04;
        }

        return min(1.0, round($score, 4));
    }

    private function trimOutliers(Collection $adjusted): Collection
    {
        if ($adjusted->count() < 4) {
            return $adjusted->values();
        }

        $prices = $adjusted->pluck('adjusted_price')->sort()->values();
        $q1 = (float) $prices->get((int) floor(($prices->count() - 1) * 0.25));
        $q3 = (float) $prices->get((int) floor(($prices->count() - 1) * 0.75));
        $iqr = max(1, $q3 - $q1);
        $lower = $q1 - (1.5 * $iqr);
        $upper = $q3 + (1.5 * $iqr);

        $trimmed = $adjusted
            ->filter(fn (array $item) => $item['adjusted_price'] >= $lower && $item['adjusted_price'] <= $upper)
            ->values();

        return $trimmed->isNotEmpty() ? $trimmed : $adjusted->values();
    }

    private function weightedAverage(Collection $items): float
    {
        $numerator = $items->sum(fn (array $item) => $item['adjusted_price'] * max(0.1, $item['fit_score']));
        $denominator = $items->sum(fn (array $item) => max(0.1, $item['fit_score']));

        return $denominator > 0 ? $numerator / $denominator : 0.0;
    }

    private function weightedMedian(Collection $items): float
    {
        $sorted = $items->sortBy('adjusted_price')->values();
        $totalWeight = $sorted->sum(fn (array $item) => max(0.1, $item['fit_score']));
        $threshold = $totalWeight / 2;
        $running = 0.0;

        foreach ($sorted as $item) {
            $running += max(0.1, $item['fit_score']);
            if ($running >= $threshold) {
                return (float) $item['adjusted_price'];
            }
        }

        return (float) ($sorted->last()['adjusted_price'] ?? 0.0);
    }

    private function dispersion(Collection $items, float $center): float
    {
        if ($items->isEmpty() || $center <= 0) {
            return 0.18;
        }

        $meanDeviation = $items->avg(fn (array $item) => abs($item['adjusted_price'] - $center));

        return max(0.05, min(0.24, ((float) $meanDeviation) / $center));
    }

    private function bandVariance(float $confidence, float $dispersion): float
    {
        $confidenceFactor = max(0.05, 0.17 - (($confidence - 50) / 320));

        return max(0.05, min(0.18, ($confidenceFactor * 0.55) + ($dispersion * 0.45)));
    }

    private function matchLabel(float $fitScore): string
    {
        return match (true) {
            $fitScore >= 0.85 => 'muy similar',
            $fitScore >= 0.70 => 'similar',
            default => 'referencia ampliada',
        };
    }

    private function roundCrc(float $amount): float
    {
        return (float) (round($amount / 50000) * 50000);
    }

    private function shareToken(): string
    {
        do {
            $token = Str::lower(Str::random(12));
        } while (VehicleValuation::query()->where('share_token', $token)->exists());

        return $token;
    }
}
