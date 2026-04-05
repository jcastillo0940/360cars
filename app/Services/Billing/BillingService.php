<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public function subscribe(User $user, Plan $plan, array $payload = []): array
    {
        $this->assertUserCanSubscribeToPlan($user, $plan);

        return DB::transaction(function () use ($user, $plan, $payload) {
            $this->expireActiveSubscriptions($user);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => ($payload['activate_now'] ?? true) ? 'active' : 'pending',
                'starts_at' => ($payload['activate_now'] ?? true) ? now() : null,
                'ends_at' => ($payload['activate_now'] ?? true) && $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                'auto_renews' => (bool) ($payload['auto_renews'] ?? false),
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'external_reference' => $payload['external_reference'] ?? 'sub_'.Str::upper(Str::random(12)),
                'metadata' => [
                    'provider' => $payload['provider'] ?? 'sandbox',
                    'payment_method' => $payload['payment_method'] ?? 'sandbox',
                ],
            ]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payable_type' => Subscription::class,
                'payable_id' => $subscription->id,
                'provider' => $payload['provider'] ?? 'sandbox',
                'payment_method' => $payload['payment_method'] ?? 'sandbox',
                'status' => ($payload['activate_now'] ?? true) ? 'paid' : 'pending',
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'external_reference' => $payload['transaction_external_reference'] ?? 'txn_'.Str::upper(Str::random(12)),
                'payload' => array_merge([
                    'plan_slug' => $plan->slug,
                    'plan_name' => $plan->name,
                ], $payload['payload'] ?? []),
                'paid_at' => ($payload['activate_now'] ?? true) ? now() : null,
            ]);

            return [
                'subscription' => $subscription->load('plan'),
                'transaction' => $transaction->load('plan'),
            ];
        });
    }

    public function activatePaidPlan(User $user, Plan $plan, array $payload = []): array
    {
        $this->assertUserCanSubscribeToPlan($user, $plan);

        return DB::transaction(function () use ($user, $plan, $payload) {
            $this->expireActiveSubscriptions($user);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'ends_at' => $plan->duration_days ? now()->addDays($plan->duration_days) : null,
                'auto_renews' => (bool) ($payload['auto_renews'] ?? false),
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'external_reference' => $payload['external_reference'] ?? 'sub_'.Str::upper(Str::random(12)),
                'metadata' => [
                    'provider' => $payload['provider'] ?? 'paypal',
                    'payment_method' => $payload['payment_method'] ?? 'paypal',
                ],
            ]);

            if (isset($payload['transaction']) && $payload['transaction'] instanceof Transaction) {
                $transaction = $payload['transaction'];
                $transaction->forceFill([
                    'payable_type' => Subscription::class,
                    'payable_id' => $subscription->id,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payload' => array_merge($transaction->payload ?? [], $payload['payload'] ?? []),
                ])->save();
            } else {
                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payable_type' => Subscription::class,
                    'payable_id' => $subscription->id,
                    'provider' => $payload['provider'] ?? 'paypal',
                    'payment_method' => $payload['payment_method'] ?? 'paypal',
                    'status' => 'paid',
                    'amount' => $plan->price,
                    'currency' => $plan->currency,
                    'external_reference' => $payload['transaction_external_reference'] ?? 'txn_'.Str::upper(Str::random(12)),
                    'payload' => array_merge([
                        'plan_slug' => $plan->slug,
                        'plan_name' => $plan->name,
                    ], $payload['payload'] ?? []),
                    'paid_at' => now(),
                ]);
            }

            return [
                'subscription' => $subscription->load('plan'),
                'transaction' => $transaction->fresh('plan'),
            ];
        });
    }

    public function assertUserCanSubscribeToPlan(User $user, Plan $plan): void
    {
        if ($user->hasRole('buyer')) {
            throw ValidationException::withMessages([
                'plan_slug' => ['Los compradores no pueden activar planes de publicacion.'],
            ]);
        }

        if ($plan->audience === 'dealer' && ! $user->hasRole('dealer', 'admin')) {
            throw ValidationException::withMessages([
                'plan_slug' => ['Este plan es exclusivo para agencias.'],
            ]);
        }
    }

    private function expireActiveSubscriptions(User $user): void
    {
        Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'expired', 'ends_at' => now()]);
    }
}
