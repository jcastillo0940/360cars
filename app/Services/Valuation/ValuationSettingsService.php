<?php

namespace App\Services\Valuation;

use App\Models\AppSetting;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ValuationSettingsService
{
    private const CACHE_PREFIX = 'app-setting:';

    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever(self::CACHE_PREFIX.$key, function () use ($key, $default) {
            if (! Schema::hasTable('app_settings')) {
                return $default;
            }

            try {
                $setting = AppSetting::query()->where('key', $key)->first();
            } catch (QueryException) {
                return $default;
            }

            if (! $setting) {
                return $default;
            }

            return $this->decode($setting->value, $setting->type);
        });
    }

    public function put(string $key, mixed $value, string $type = 'string'): void
    {
        if (! Schema::hasTable('app_settings')) {
            return;
        }

        AppSetting::query()->updateOrCreate(
            ['key' => $key],
            [
                'value' => $this->encode($value, $type),
                'type' => $type,
            ],
        );

        Cache::forget(self::CACHE_PREFIX.$key);
    }

    public function valuationAiEnabled(): bool
    {
        return (bool) $this->get('valuation.ai_enabled', (bool) config('valuation.ai.enabled_by_default', false));
    }

    private function encode(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) ((int) $value),
            'float' => (string) ((float) $value),
            'json' => json_encode($value, JSON_THROW_ON_ERROR),
            default => (string) $value,
        };
    }

    private function decode(?string $value, ?string $type): mixed
    {
        return match ($type) {
            'boolean' => $value === '1',
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => $value ? json_decode($value, true, 512, JSON_THROW_ON_ERROR) : null,
            default => $value,
        };
    }
}
