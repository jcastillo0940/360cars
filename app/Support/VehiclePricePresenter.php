<?php

namespace App\Support;

class VehiclePricePresenter
{
    public static function present(float $amount, ?string $currency = 'CRC', ?array $quote = null): array
    {
        $currency = strtoupper((string) ($currency ?: 'CRC'));
        $usdToCrc = (float) data_get($quote, 'usd_to_crc', 0);

        if ($currency === 'USD') {
            $usdRaw = $amount;
            $crcRaw = $usdToCrc > 0 ? $amount * $usdToCrc : null;
        } else {
            $crcRaw = $amount;
            $usdRaw = $usdToCrc > 0 ? $amount / $usdToCrc : null;
        }

        $primaryRaw = $crcRaw ?? $amount;

        return [
            'primary_raw' => $primaryRaw,
            'primary_formatted' => self::formatCrc($primaryRaw),
            'secondary_raw' => $usdRaw,
            'secondary_formatted' => $usdRaw ? '˜ '.self::formatUsd($usdRaw) : null,
            'base_currency' => $currency,
            'exchange_source' => data_get($quote, 'source'),
            'exchange_fetched_at' => data_get($quote, 'fetched_at'),
            'exchange_stale' => (bool) data_get($quote, 'stale', false),
        ];
    }

    public static function formatCrc(float $amount): string
    {
        return '¢'.number_format(round($amount), 0, ',', '.');
    }

    public static function formatUsd(float $amount): string
    {
        return 'US$'.number_format(round($amount), 0, '.', ',');
    }
}
