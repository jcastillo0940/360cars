<?php

namespace App\Services\Billing;

use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

class PayPalWebhookService
{
    public function __construct(
        private readonly PayPalClient $client,
        private readonly PayPalCheckoutService $checkoutService,
    ) {
    }

    public function verifySignature(array $headers, array $event): bool
    {
        $webhookId = (string) config('paypal.webhook_id');

        if ($webhookId === '') {
            throw ValidationException::withMessages([
                'webhook' => ['PAYPAL_WEBHOOK_ID no esta configurado.'],
            ]);
        }

        $response = $this->client->verifyWebhookSignature([
            'auth_algo' => $headers['paypal-auth-algo'] ?? '',
            'cert_url' => $headers['paypal-cert-url'] ?? '',
            'transmission_id' => $headers['paypal-transmission-id'] ?? '',
            'transmission_sig' => $headers['paypal-transmission-sig'] ?? '',
            'transmission_time' => $headers['paypal-transmission-time'] ?? '',
            'webhook_id' => $webhookId,
            'webhook_event' => $event,
        ]);

        return strtoupper((string) Arr::get($response, 'verification_status')) === 'SUCCESS';
    }

    public function handle(array $event): void
    {
        $eventType = (string) Arr::get($event, 'event_type');

        match ($eventType) {
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($event),
            'PAYMENT.CAPTURE.PENDING' => $this->handlePending($event),
            'PAYMENT.CAPTURE.DENIED', 'CHECKOUT.PAYMENT-APPROVAL.REVERSED' => $this->handleFailed($event),
            default => null,
        };
    }

    private function handleCaptureCompleted(array $event): void
    {
        $orderId = (string) Arr::get($event, 'resource.supplementary_data.related_ids.order_id', '');
        if ($orderId === '') {
            return;
        }

        $transaction = \App\Models\Transaction::query()
            ->with(['plan', 'user', 'payable'])
            ->where('provider', 'paypal')
            ->where('external_reference', $orderId)
            ->first();

        if (! $transaction) {
            return;
        }

        $transaction->forceFill([
            'payload' => array_merge($transaction->payload ?? [], ['paypal_webhook' => $event]),
        ])->save();

        $this->checkoutService->finalizeSuccessfulCapture($transaction, Arr::get($event, 'resource', []));
    }

    private function handlePending(array $event): void
    {
        $orderId = (string) Arr::get($event, 'resource.supplementary_data.related_ids.order_id', '');
        if ($orderId === '') {
            return;
        }

        $transaction = \App\Models\Transaction::query()
            ->where('provider', 'paypal')
            ->where('external_reference', $orderId)
            ->first();

        if ($transaction) {
            $transaction->forceFill([
                'status' => 'pending',
                'payload' => array_merge($transaction->payload ?? [], ['paypal_webhook' => $event]),
            ])->save();
        }
    }

    private function handleFailed(array $event): void
    {
        $orderId = (string) Arr::get($event, 'resource.supplementary_data.related_ids.order_id', Arr::get($event, 'resource.id', ''));
        if ($orderId === '') {
            return;
        }

        $transaction = \App\Models\Transaction::query()
            ->where('provider', 'paypal')
            ->where('external_reference', $orderId)
            ->first();

        if ($transaction) {
            $transaction->forceFill([
                'status' => 'failed',
                'payload' => array_merge($transaction->payload ?? [], ['paypal_webhook' => $event]),
            ])->save();
        }
    }
}
