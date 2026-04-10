<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PayPalCheckoutService
{
    public function __construct(
        private readonly PayPalClient $client,
        private readonly BillingService $billingService,
    ) {
    }

    public function createOrder(User $user, Plan $plan, array $urls = []): array
    {
        $this->billingService->assertUserCanSubscribeToPlan($user, $plan);

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'reference_id' => 'plan-'.$plan->id,
                'custom_id' => 'user:'.$user->id.'|plan:'.$plan->id,
                'description' => 'Suscripcion '.$plan->name.' - Movikaa',
                'amount' => [
                    'currency_code' => $plan->currency ?: config('paypal.currency', 'USD'),
                    'value' => number_format((float) $plan->price, 2, '.', ''),
                ],
            ]],
            'application_context' => array_filter([
                'brand_name' => config('paypal.brand_name'),
                'user_action' => 'PAY_NOW',
                'return_url' => $urls['return_url'] ?? config('paypal.return_url'),
                'cancel_url' => $urls['cancel_url'] ?? config('paypal.cancel_url'),
            ]),
        ];

        $order = $this->client->createOrder($payload);
        $paypalOrderId = (string) Arr::get($order, 'id');

        if ($paypalOrderId === '') {
            throw new \RuntimeException('PayPal no devolvio un order id valido.');
        }

        $transaction = Transaction::updateOrCreate(
            [
                'external_reference' => $paypalOrderId,
                'provider' => 'paypal',
            ],
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_method' => 'paypal',
                'status' => 'pending',
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'payload' => [
                    'paypal_order' => $order,
                    'plan_slug' => $plan->slug,
                    'plan_name' => $plan->name,
                ],
            ],
        );

        return [
            'paypal_order_id' => $paypalOrderId,
            'approve_url' => $this->findApproveUrl($order),
            'order' => $order,
            'transaction' => $transaction->fresh('plan'),
        ];
    }

    public function captureOrder(User $user, string $paypalOrderId): array
    {
        $transaction = Transaction::query()
            ->with(['plan', 'payable'])
            ->where('provider', 'paypal')
            ->where('external_reference', $paypalOrderId)
            ->firstOrFail();

        if ($transaction->user_id !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'No puedes capturar est? orden de PayPal.');
        }

        if ($transaction->status === 'paid' && $transaction->payable_type && $transaction->payable_id) {
            return [
                'transaction' => $transaction->fresh(['plan', 'payable.plan']),
                'subscription' => $transaction->payable,
                'paypal_capture' => $transaction->payload['paypal_capture'] ?? null,
            ];
        }

        $capture = $this->client->captureOrder($paypalOrderId);
        return $this->finalizeSuccessfulCapture($transaction, $capture);
    }

    public function finalizeSuccessfulCapture(Transaction $transaction, array $capture): array
    {
        $captureStatus = strtoupper((string) Arr::get($capture, 'status'));

        if ($captureStatus !== 'COMPLETED') {
            $transaction->forceFill([
                'status' => 'failed',
                'payload' => array_merge($transaction->payload ?? [], ['paypal_capture' => $capture]),
            ])->save();

            throw ValidationException::withMessages([
                'paypal_order_id' => ['PayPal no devolvio una captura completada para est? orden.'],
            ]);
        }

        return DB::transaction(function () use ($transaction, $capture) {
            $transaction->refresh();

            if ($transaction->status === 'paid' && $transaction->payable) {
                return [
                    'transaction' => $transaction->fresh(['plan', 'payable.plan']),
                    'subscription' => $transaction->payable,
                    'paypal_capture' => $capture,
                ];
            }

            $subscriptionData = $this->billingService->activatePaidPlan($transaction->user, $transaction->plan, [
                'provider' => 'paypal',
                'payment_method' => 'paypal',
                'auto_renews' => false,
                'external_reference' => (string) Arr::get($capture, 'id', 'cap_'.Str::upper(Str::random(10))),
                'transaction' => $transaction,
                'payload' => ['paypal_capture' => $capture],
            ]);

            return [
                'transaction' => $subscriptionData['transaction']->fresh(['plan', 'payable.plan']),
                'subscription' => $subscriptionData['subscription'],
                'paypal_capture' => $capture,
            ];
        });
    }

    private function findApproveUrl(array $order): ?string
    {
        foreach (Arr::get($order, 'links', []) as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                return $link['href'] ?? null;
            }
        }

        return null;
    }
}

