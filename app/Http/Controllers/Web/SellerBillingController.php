<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Billing\BillingService;
use App\Services\Billing\PayPalCheckoutService;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SellerBillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly PayPalCheckoutService $payPalCheckoutService,
        private readonly ValuationSettingsService $settingsService,
    ) {
    }

    public function subscribeSandbox(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
        ]);

        $plan = Plan::query()->where('slug', $data['plan_slug'])->firstOrFail();

        $this->billingService->subscribe($request->user(), $plan, [
            'provider' => 'internal',
            'payment_method' => 'internal',
            'auto_renews' => false,
            'activate_now' => true,
        ]);

        return redirect()->to(route('seller.dashboard').'#billing')->with('status', 'Plan activado correctamente.');
    }

    public function activateFree(Request $request): RedirectResponse
    {
        $plan = Plan::query()->where('slug', 'basico')->firstOrFail();

        $this->billingService->subscribe($request->user(), $plan, [
            'provider' => 'internal',
            'payment_method' => 'free',
            'auto_renews' => false,
            'activate_now' => true,
        ]);

        return redirect()->route('seller.billing')->with('status', 'Plan basico activado correctamente.');
    }

    public function requestManualPayment(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
            'payment_method' => ['required', 'string', 'in:cash,bank_transfer,sinpe_movil,tilopay'],
        ]);

        $plan = Plan::query()->where('slug', $data['plan_slug'])->firstOrFail();

        $this->billingService->subscribe($request->user(), $plan, [
            'provider' => in_array($data['payment_method'], ['cash', 'bank_transfer', 'sinpe_movil'], true) ? 'offline' : 'tilopay',
            'payment_method' => $data['payment_method'],
            'auto_renews' => false,
            'activate_now' => false,
            'payload' => [
                'requested_from' => 'seller_billing',
            ],
        ]);

        return redirect()->route('seller.billing')->with('status', 'Solicitud de pago registrada. El owner podra verificarla y activar tu plan.');
    }

    public function createPayPalOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
        ]);

        $plan = Plan::query()->where('slug', $data['plan_slug'])->firstOrFail();

        try {
            $result = $this->payPalCheckoutService->createOrder($request->user(), $plan, [
                'return_url' => route('seller.billing.paypal.return'),
                'cancel_url' => route('seller.billing'),
            ]);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['billing' => $exception->getMessage()]);
        }

        if (! filled($result['approve_url'] ?? null)) {
            return back()->withErrors(['billing' => 'PayPal no devolvio una URL de aprobacion valida.']);
        }

        return redirect()->away($result['approve_url']);
    }

    public function capturePayPalReturn(Request $request): RedirectResponse
    {
        $paypalOrderId = (string) $request->query('token', '');

        if ($paypalOrderId === '') {
            return redirect()->route('seller.billing')->withErrors(['billing' => 'PayPal no devolvio el token de la orden.']);
        }

        try {
            $this->payPalCheckoutService->captureOrder($request->user(), $paypalOrderId);
        } catch (\Throwable $exception) {
            return redirect()->route('seller.billing')->withErrors(['billing' => $exception->getMessage()]);
        }

        return redirect()->route('seller.billing')->with('status', 'Pago PayPal capturado y suscripcion activada correctamente.');
    }
}
