<?php

namespace App\Services\Billing;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public function subscribe(User $user, Plan $plan, array $payload = []): array
    {
        $this->assertUserCanSubscribeToPlan($user, $plan);
        $this->synchronizeUserSubscriptions($user);

        return DB::transaction(function () use ($user, $plan, $payload) {
            $activeSubscription = $this->activeSubscription($user);
            $existingPending = $this->scheduledSubscription($user, $plan);

            if ($activeSubscription?->plan_id === $plan->id) {
                throw ValidationException::withMessages([
                    'plan_slug' => ['Ya tienes este plan activo.'],
                ]);
            }

            if ($existingPending) {
                throw ValidationException::withMessages([
                    'plan_slug' => ['Ya tienes este plan programado para el siguiente ciclo.'],
                ]);
            }

            $scheduleAt = $this->resolveScheduleStart($activeSubscription, $payload);
            $activateNow = is_null($scheduleAt) && (bool) ($payload['activate_now'] ?? true);
            $paymentMethod = (string) ($payload['payment_method'] ?? 'internal');
            $provider = (string) ($payload['provider'] ?? 'internal');
            $prepaid = $this->paymentIsAlreadyConfirmed($provider, $paymentMethod, $payload);
            $startsAt = $activateNow ? now() : $scheduleAt;
            $endsAt = $this->resolveEndDate($plan, $startsAt, $activateNow);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $activateNow ? 'active' : 'pending',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'auto_renews' => (bool) ($payload['auto_renews'] ?? false),
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'external_reference' => $payload['external_reference'] ?? 'sub_'.Str::upper(Str::random(12)),
                'metadata' => array_filter([
                    'provider' => $provider,
                    'payment_method' => $paymentMethod,
                    'scheduled_change' => ! $activateNow,
                    'activate_on' => $scheduleAt?->toIso8601String(),
                    'requested_from' => data_get($payload, 'payload.requested_from'),
                ], fn ($value) => ! is_null($value)),
            ]);

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payable_type' => Subscription::class,
                'payable_id' => $subscription->id,
                'provider' => $provider,
                'payment_method' => $paymentMethod,
                'status' => $prepaid ? 'paid' : 'pending',
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'external_reference' => $payload['transaction_external_reference'] ?? 'txn_'.Str::upper(Str::random(12)),
                'payload' => array_merge([
                    'plan_slug' => $plan->slug,
                    'plan_name' => $plan->name,
                    'scheduled_change' => ! $activateNow,
                    'activate_on' => $scheduleAt?->toIso8601String(),
                ], $payload['payload'] ?? []),
                'paid_at' => $prepaid ? now() : null,
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
        $this->synchronizeUserSubscriptions($user);

        return DB::transaction(function () use ($user, $plan, $payload) {
            $activeSubscription = $this->activeSubscription($user);
            $existingPending = $this->scheduledSubscription($user, $plan);

            if ($activeSubscription?->plan_id === $plan->id) {
                throw ValidationException::withMessages([
                    'plan_slug' => ['Ya tienes este plan activo.'],
                ]);
            }

            if ($existingPending) {
                return [
                    'subscription' => $existingPending->load('plan'),
                    'transaction' => isset($payload['transaction']) && $payload['transaction'] instanceof Transaction
                        ? $payload['transaction']->fresh('plan')
                        : Transaction::query()->whereMorphedTo('payable', $existingPending)->latest('id')->first(),
                ];
            }

            $scheduleAt = $this->resolveScheduleStart($activeSubscription, ['defer_if_active' => true]);
            $activateNow = is_null($scheduleAt);
            $startsAt = $activateNow ? now() : $scheduleAt;
            $endsAt = $this->resolveEndDate($plan, $startsAt, $activateNow);

            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $activateNow ? 'active' : 'pending',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'auto_renews' => (bool) ($payload['auto_renews'] ?? false),
                'amount' => $plan->price,
                'currency' => $plan->currency,
                'external_reference' => $payload['external_reference'] ?? 'sub_'.Str::upper(Str::random(12)),
                'metadata' => array_filter([
                    'provider' => $payload['provider'] ?? 'paypal',
                    'payment_method' => $payload['payment_method'] ?? 'paypal',
                    'scheduled_change' => ! $activateNow,
                    'activate_on' => $scheduleAt?->toIso8601String(),
                ], fn ($value) => ! is_null($value)),
            ]);

            if (isset($payload['transaction']) && $payload['transaction'] instanceof Transaction) {
                $transaction = $payload['transaction'];
                $transaction->forceFill([
                    'payable_type' => Subscription::class,
                    'payable_id' => $subscription->id,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payload' => array_merge($transaction->payload ?? [], $payload['payload'] ?? [], [
                        'scheduled_change' => ! $activateNow,
                        'activate_on' => $scheduleAt?->toIso8601String(),
                    ]),
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
                        'scheduled_change' => ! $activateNow,
                        'activate_on' => $scheduleAt?->toIso8601String(),
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

    public function approvePendingTransaction(Transaction $transaction): array
    {
        $transaction->loadMissing(['user', 'plan', 'payable.plan']);

        if ($transaction->status === 'paid') {
            return [
                'subscription' => $transaction->payable,
                'transaction' => $transaction,
            ];
        }

        if (! $transaction->user || ! $transaction->plan) {
            throw ValidationException::withMessages([
                'transaction' => ['La transaccion no tiene usuario o plan asociado.'],
            ]);
        }

        $this->synchronizeUserSubscriptions($transaction->user);

        if ($transaction->payable instanceof Subscription) {
            return DB::transaction(function () use ($transaction) {
                $subscription = $transaction->payable->fresh('plan');
                $activeSubscription = $this->activeSubscription($transaction->user);
                $shouldSchedule = $activeSubscription
                    && $activeSubscription->id !== $subscription->id
                    && $activeSubscription->ends_at
                    && $activeSubscription->ends_at->isFuture();

                $startsAt = $shouldSchedule ? $activeSubscription->ends_at->copy() : now();
                $subscription->forceFill([
                    'status' => $shouldSchedule ? 'pending' : 'active',
                    'starts_at' => $startsAt,
                    'ends_at' => $this->resolveEndDate($subscription->plan, $startsAt, ! $shouldSchedule),
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'scheduled_change' => $shouldSchedule,
                        'activate_on' => $shouldSchedule ? $startsAt->toIso8601String() : null,
                        'approved_manually' => true,
                    ]),
                ])->save();

                $transaction->forceFill([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payload' => array_merge($transaction->payload ?? [], [
                        'approved_manually' => true,
                        'scheduled_change' => $shouldSchedule,
                        'activate_on' => $shouldSchedule ? $startsAt->toIso8601String() : null,
                    ]),
                ])->save();

                return [
                    'subscription' => $subscription->fresh('plan'),
                    'transaction' => $transaction->fresh('plan'),
                ];
            });
        }

        return $this->activatePaidPlan($transaction->user, $transaction->plan, [
            'provider' => $transaction->provider,
            'payment_method' => $transaction->payment_method ?? $transaction->provider,
            'transaction' => $transaction,
            'payload' => array_merge($transaction->payload ?? [], ['approved_manually' => true]),
        ]);
    }

    public function rejectPendingTransaction(Transaction $transaction): void
    {
        $transaction->forceFill([
            'status' => 'failed',
            'paid_at' => null,
            'payload' => array_merge($transaction->payload ?? [], ['rejected_manually' => true]),
        ])->save();

        if ($transaction->payable instanceof Subscription && $transaction->payable->status === 'pending') {
            $transaction->payable->update([
                'status' => 'cancelled',
                'ends_at' => now(),
            ]);
        }
    }

    public function synchronizeUserSubscriptions(User $user): void
    {
        Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->update(['status' => 'expired']);

        $active = $this->activeSubscription($user);

        if ($active) {
            return;
        }

        $scheduled = Subscription::query()
            ->with('plan')
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->orderBy('starts_at')
            ->orderBy('id')
            ->first();

        if (! $scheduled) {
            return;
        }

        DB::transaction(function () use ($scheduled): void {
            $startsAt = $scheduled->starts_at && $scheduled->starts_at->isFuture() ? $scheduled->starts_at : now();
            $scheduled->forceFill([
                'status' => 'active',
                'starts_at' => $startsAt,
                'ends_at' => $this->resolveEndDate($scheduled->plan, $startsAt, true),
                'metadata' => array_merge($scheduled->metadata ?? [], [
                    'scheduled_change' => false,
                    'activated_from_queue' => true,
                ]),
            ])->save();
        });
    }

    public function assertUserCanSubscribeToPlan(User $user, Plan $plan): void
    {
        if ($plan->audience === 'dealer' && ! $user->hasRole('dealer', 'admin')) {
            throw ValidationException::withMessages([
                'plan_slug' => ['Este plan es exclusivo para agencias.'],
            ]);
        }
    }

    private function activeSubscription(User $user): ?Subscription
    {
        return Subscription::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest('starts_at')
            ->latest('id')
            ->first();
    }

    private function scheduledSubscription(User $user, Plan $plan): ?Subscription
    {
        return Subscription::query()
            ->where('user_id', $user->id)
            ->where('plan_id', $plan->id)
            ->where('status', 'pending')
            ->where(function ($query): void {
                $query->whereNull('starts_at')->orWhere('starts_at', '>=', now());
            })
            ->latest('starts_at')
            ->latest('id')
            ->first();
    }

    private function resolveScheduleStart(?Subscription $activeSubscription, array $payload): ?Carbon
    {
        $shouldDefer = (bool) ($payload['defer_if_active'] ?? true);

        if (! $shouldDefer || ! $activeSubscription || ! $activeSubscription->ends_at || ! $activeSubscription->ends_at->isFuture()) {
            return null;
        }

        return $activeSubscription->ends_at->copy();
    }

    private function resolveEndDate(?Plan $plan, ?Carbon $startsAt, bool $isEffective): ?Carbon
    {
        if (! $plan?->duration_days || ! $startsAt) {
            return null;
        }

        return $startsAt->copy()->addDays($plan->duration_days);
    }

    private function paymentIsAlreadyConfirmed(string $provider, string $paymentMethod, array $payload): bool
    {
        if (array_key_exists('transaction_paid', $payload)) {
            return (bool) $payload['transaction_paid'];
        }

        return in_array($provider, ['internal', 'sandbox'], true) || in_array($paymentMethod, ['free', 'internal', 'sandbox'], true);
    }
}
