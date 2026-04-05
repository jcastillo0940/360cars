<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\Billing\BillingService;
use App\Services\Billing\PayPalCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class SellerBillingController extends Controller
{
    public function __construct(
        private readonly BillingService $billingService,
        private readonly PayPalCheckoutService $payPalCheckoutService,
    ) {
    }

    public function subscribeSandbox(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plan_slug' => ['required', 'string', 'exists:plans,slug'],
        ]);

        $plan = Plan::query()->where('slug', $data['plan_slug'])->firstOrFail();

        $this->billingService->subscribe($request->user(), $plan, [
            'provider' => 'sandbox',
            'payment_method' => 'sandbox',
            'auto_renews' => false,
            'activate_now' => true,
        ]);

        return redirect()->to(route('seller.dashboard').'#billing')->with('status', 'Plan activado correctamente en modo sandbox.');
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
                'cancel_url' => route('seller.dashboard').'#billing',
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
            return redirect()->to(route('seller.dashboard').'#billing')->withErrors(['billing' => 'PayPal no devolvio el token de la orden.']);
        }

        try {
            $this->payPalCheckoutService->captureOrder($request->user(), $paypalOrderId);
        } catch (\Throwable $exception) {
            return redirect()->to(route('seller.dashboard').'#billing')->withErrors(['billing' => $exception->getMessage()]);
        }

        return redirect()->to(route('seller.dashboard').'#billing')->with('status', 'Pago PayPal capturado y suscripcion activada correctamente.');
    }
}
