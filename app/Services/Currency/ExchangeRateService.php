<?php

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class ExchangeRateService
{
    private const CACHE_KEY = 'exchange_rates:usd_crc';

    public function latest(bool $forceRefresh = false): array
    {
        if (app()->environment('testing')) {
            return $this->normalizeQuote((float) config('exchange-rates.test_usd_to_crc', 505.0), 'testing', now()->toIso8601String(), false);
        }

        $cached = Cache::get(self::CACHE_KEY);

        if (! $forceRefresh && $this->isFresh($cached)) {
            return $cached + ['stale' => false];
        }

        $fresh = $this->fetch();

        if ($fresh !== null) {
            Cache::put(self::CACHE_KEY, $fresh, now()->addHours((int) config('exchange-rates.cache_hours', 12)));

            return $fresh + ['stale' => false];
        }

        if (is_array($cached)) {
            return $cached + ['stale' => true];
        }

        return $this->normalizeQuote(null, 'unavailable', null, true);
    }

    public function refresh(): array
    {
        return $this->latest(true);
    }

    private function fetch(): ?array
    {
        foreach ((array) config('exchange-rates.providers', []) as $provider) {
            try {
                $response = Http::timeout(8)
                    ->acceptJson()
                    ->get($provider['url'] ?? '');

                if (! $response->successful()) {
                    continue;
                }

                $payload = $response->json();
                $rate = $this->extractRate($provider['name'] ?? 'provider', is_array($payload) ? $payload : []);

                if ($rate !== null && $rate > 0) {
                    return $this->normalizeQuote((float) $rate, (string) ($provider['name'] ?? 'provider'), now()->toIso8601String(), false);
                }
            } catch (Throwable) {
                continue;
            }
        }

        return null;
    }

    private function extractRate(string $provider, array $payload): ?float
    {
        return match ($provider) {
            'open.er-api' => isset($payload['rates']['CRC']) ? (float) $payload['rates']['CRC'] : null,
            'frankfurter' => isset($payload['rates']['CRC']) ? (float) $payload['rates']['CRC'] : null,
            default => null,
        };
    }

    private function isFresh(mixed $cached): bool
    {
        if (! is_array($cached) || empty($cached['fetched_at'])) {
            return false;
        }

        $fetchedAt = strtotime((string) $cached['fetched_at']);

        if (! $fetchedAt) {
            return false;
        }

        $maxAge = max(1, (int) config('exchange-rates.cache_hours', 12)) * 3600;

        return (time() - $fetchedAt) < $maxAge;
    }

    private function normalizeQuote(?float $usdToCrc, string $source, ?string $fetchedAt, bool $stale): array
    {
        return [
            'usd_to_crc' => $usdToCrc,
            'crc_to_usd' => $usdToCrc && $usdToCrc > 0 ? 1 / $usdToCrc : null,
            'source' => $source,
            'fetched_at' => $fetchedAt,
            'stale' => $stale,
        ];
    }
}
