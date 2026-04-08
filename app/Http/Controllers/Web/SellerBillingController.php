<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Billing\BillingService;
use App\Services\Billing\PayPalCheckoutService;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
        return $this->freeModeRedirect();
    }

    public function activateFree(Request $request): RedirectResponse
    {
        return $this->freeModeRedirect();
    }

    public function requestManualPayment(Request $request): RedirectResponse
    {
        return $this->freeModeRedirect();
    }

    public function createPayPalOrder(Request $request): RedirectResponse
    {
        return $this->freeModeRedirect();
    }

    public function capturePayPalReturn(Request $request): RedirectResponse
    {
        return $this->freeModeRedirect();
    }

    private function freeModeRedirect(): RedirectResponse
    {
        return redirect()
            ->route('seller.billing')
            ->with('status', 'Las publicaciones están temporalmente gratis e ilimitadas para todos los usuarios registrados. No necesitas activar ni pagar un plan.');
    }
}