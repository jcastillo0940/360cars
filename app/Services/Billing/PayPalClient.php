<?php

namespace App\Services\Billing;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayPalClient
{
    public function createOrder(array $payload): array
    {
        return $this->request()
            ->withToken($this->accessToken())
            ->withHeaders(['PayPal-Request-Id' => (string) Str::uuid()])
            ->post('/v2/checkout/orders', $payload)
            ->throw()
            ->json();
    }

    public function captureOrder(string $paypalOrderId): array
    {
        return $this->request()
            ->withToken($this->accessToken())
            ->withHeaders(['PayPal-Request-Id' => (string) Str::uuid()])
            ->post('/v2/checkout/orders/'.$paypalOrderId.'/capture')
            ->throw()
            ->json();
    }

    public function verifyWebhookSignature(array $payload): array
    {
        return $this->request()
            ->withToken($this->accessToken())
            ->post('/v1/notifications/verify-webhook-signature', $payload)
            ->throw()
            ->json();
    }

    private function accessToken(): string
    {
        $clientId = (string) config('paypal.client_id');
        $clientSecret = (string) config('paypal.client_secret');

        if ($clientId === '' || $clientSecret === '') {
            throw new RuntimeException('PayPal no est? configurado. Debes definir PAYPAL_CLIENT_ID y PAYPAL_CLIENT_SECRET.');
        }

        $response = $this->request()
            ->asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->post('/v1/oauth2/token', ['grant_type' => 'client_credentials'])
            ->throw()
            ->json();

        $token = Arr::get($response, 'access_token');

        if (! is_string($token) || $token === '') {
            throw new RuntimeException('PayPal no devolvio un access token valido.');
        }

        return $token;
    }

    private function request(): PendingRequest
    {
        $mode = (string) config('paypal.mode', 'sandbox');
        $baseUrl = (string) config('paypal.base_urls.'.$mode, config('paypal.base_urls.sandbox'));

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->timeout(30);
    }
}
