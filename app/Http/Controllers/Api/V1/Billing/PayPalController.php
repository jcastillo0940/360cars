<?php

namespace App\Http\Controllers\Api\V1\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\CapturePayPalOrderRequest;
use App\Http\Requests\Billing\CreatePayPalOrderRequest;
use App\Http\Resources\Billing\SubscriptionResource;
use App\Http\Resources\Billing\TransactionResource;
use App\Models\Plan;
use App\Services\Billing\PayPalCheckoutService;
use App\Services\Billing\PayPalWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayPalController extends Controller
{
    public function __construct(
        private readonly PayPalCheckoutService $checkoutService,
        private readonly PayPalWebhookService $webhookService,
    ) {
    }

    public function createOrder(CreatePayPalOrderRequest $request): JsonResponse
    {
        $plan = Plan::query()->where('slug', $request->string('plan_slug')->toString())->firstOrFail();

        $result = $this->checkoutService->createOrder($request->user(), $plan, [
            'return_url' => $request->input('return_url'),
            'cancel_url' => $request->input('cancel_url'),
        ]);

        return response()->json([
            'message' => 'Orden PayPal creada correctamente.',
            'paypal_order_id' => $result['paypal_order_id'],
            'approve_url' => $result['approve_url'],
            'transaction' => new TransactionResource($result['transaction']),
            'paypal_order' => $result['order'],
        ], 201);
    }

    public function captureOrder(CapturePayPalOrderRequest $request): JsonResponse
    {
        $result = $this->checkoutService->captureOrder($request->user(), $request->string('paypal_order_id')->toString());

        return response()->json([
            'message' => 'Pago PayPal capturado correctamente.',
            'transaction' => new TransactionResource($result['transaction']),
            'subscription' => new SubscriptionResource($result['subscription']),
            'paypal_capture' => $result['paypal_capture'],
        ]);
    }

    public function webhook(Request $request): JsonResponse
    {
        $headers = collect($request->headers->all())->map(fn ($values) => $values[0] ?? null)->all();
        $event = $request->json()->all();

        abort_unless($this->webhookService->verifySignature($headers, $event), 400, 'Firma PayPal invalida.');

        $this->webhookService->handle($event);

        return response()->json(['message' => 'Webhook PayPal procesado.']);
    }
}
