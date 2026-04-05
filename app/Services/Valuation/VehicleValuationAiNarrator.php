<?php

namespace App\Services\Valuation;

use Illuminate\Support\Facades\Http;
use Throwable;

class VehicleValuationAiNarrator
{
    public function summarize(array $payload): ?string
    {
        if (! $this->configured()) {
            return null;
        }

        try {
            $response = Http::timeout(15)
                ->withToken((string) config('valuation.ai.api_key'))
                ->acceptJson()
                ->post((string) config('valuation.ai.endpoint'), [
                    'model' => (string) config('valuation.ai.model', 'openrouter/auto'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un tasador automotriz para Costa Rica. Resume en maximo 90 palabras la valoracion de un vehiculo, explicando depreciacion, demanda de mercado y factores relevantes. No inventes datos externos. Habla en espanol neutro.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ],
                    ],
                ]);

            if (! $response->successful()) {
                return null;
            }

            $content = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            return $content !== '' ? $content : null;
        } catch (Throwable) {
            return null;
        }
    }

    public function configured(): bool
    {
        return filled(config('valuation.ai.api_key')) && filled(config('valuation.ai.endpoint'));
    }
}
