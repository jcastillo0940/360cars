<?php

namespace App\Services\Seo;

use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IndexNowService
{
    public function __construct(
        private readonly ValuationSettingsService $settings,
    ) {
    }

    /**
     * @param  array<int, string>  $urls
     */
    public function submit(array $urls): void
    {
        $urls = collect($urls)
            ->filter(fn ($url) => is_string($url) && $url !== '')
            ->unique()
            ->values()
            ->all();

        if ($urls === [] || ! $this->enabled()) {
            return;
        }

        $key = (string) $this->settings->get('seo.indexnow_key', '');
        if ($key === '') {
            return;
        }

        $endpoint = (string) $this->settings->get('seo.indexnow_endpoint', 'https://api.indexnow.org/indexnow');
        $host = parse_url(config('app.url') ?: url('/'), PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return;
        }

        $payload = [
            'host' => $host,
            'key' => $key,
            'keyLocation' => route('indexnow.key'),
            'urlList' => $urls,
        ];

        try {
            Http::timeout(8)->acceptJson()->post($endpoint, $payload)->throw();
        } catch (\Throwable $exception) {
            Log::warning('seo.indexnow.submit_failed', [
                'message' => $exception->getMessage(),
                'urls' => $urls,
            ]);
        }
    }

    public function enabled(): bool
    {
        return (bool) $this->settings->get('seo.indexnow_enabled', false);
    }

    public function key(): string
    {
        return (string) $this->settings->get('seo.indexnow_key', '');
    }
}
