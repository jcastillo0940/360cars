<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\SubscribeRequest;
use App\Http\Resources\Billing\SubscriptionResource;
use App\Http\Resources\Billing\TransactionResource;
use App\Models\Plan;
use App\Services\Billing\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly BillingService $billingService)
    {
    }

    public function current(Request $request): JsonResponse
    {
        $subscription = $request->user()->subscriptions()->with('plan')->latest('starts_at')->latest('id')->first();

        return response()->json([
            'data' => $subscription ? new SubscriptionResource($subscription) : null,
        ]);
    }

    public function store(SubscribeRequest $request): JsonResponse
    {
        $plan = Plan::query()->where('slug', $request->string('plan_slug')->toString())->firstOrFail();

        $result = $this->billingService->subscribe($request->user(), $plan, [
            'provider' => $request->input('provider', 'sandbox'),
            'payment_method' => $request->input('payment_method', 'sandbox'),
            'auto_renews' => $request->boolean('auto_renews'),
            'activate_now' => $request->boolean('activate_now', true),
        ]);

        return response()->json([
            'message' => 'Plan activado correctamente.',
            'subscription' => new SubscriptionResource($result['subscription']),
            'transaction' => new TransactionResource($result['transaction']),
        ], 201);
    }
}
