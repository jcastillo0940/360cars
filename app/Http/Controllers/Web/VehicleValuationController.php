<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Valuation\StoreVehicleValuationRequest;
use App\Models\VehicleMake;
use App\Models\VehicleValuation;
use App\Services\Currency\ExchangeRateService;
use App\Services\Valuation\ValuationSettingsService;
use App\Services\Valuation\VehicleValuationAiNarrator;
use App\Services\Valuation\VehicleValuationService;
use App\Support\VehiclePricePresenter;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VehicleValuationController extends Controller
{
    public function __construct(
        private readonly VehicleValuationService $valuationService,
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $valuationSettings,
        private readonly VehicleValuationAiNarrator $aiNarrator,
    ) {
    }

    public function index(): View
    {
        return view('valuation.index', [
            'valuationProps' => $this->baseProps(),
        ]);
    }

    public function store(StoreVehicleValuationRequest $request): RedirectResponse
    {
        $valuation = $this->valuationService->evaluate($request->validated(), $request->user());

        return redirect()->route('valuation.show', $valuation->share_token);
    }

    public function show(string $token): View
    {
        $valuation = VehicleValuation::query()->where('share_token', $token)->firstOrFail();
        $props = $this->baseProps();
        $props['result'] = $this->mapValuation($valuation);
        $props['shareUrl'] = route('valuation.show', $valuation->share_token);

        return view('valuation.index', [
            'valuationProps' => $props,
        ]);
    }

    public function updateAiSetting(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'valuation_ai_enabled' => ['nullable', 'boolean'],
        ]);

        $enabled = (bool) ($data['valuation_ai_enabled'] ?? false);
        $this->valuationSettings->put('valuation.ai_enabled', $enabled, 'boolean');

        return redirect()->to(route('admin.dashboard').'#valuation-ai')->with('status', $enabled
            ? 'Narrativa IA activada para nuevas evaluaciones.'
            : 'Narrativa IA desactivada. El tasador seguira funcionando con el algoritmo interno.');
    }

    private function baseProps(): array
    {
        $accountUrl = auth()->check()
            ? (auth()->user()->hasRole('admin')
                ? route('admin.dashboard')
                : (auth()->user()->hasRole('seller', 'dealer') ? route('seller.dashboard') : route('buyer.dashboard')))
            : route('login');

        $sellUrl = auth()->check() && auth()->user()->hasRole('seller', 'dealer', 'admin')
            ? route('seller.dashboard')
            : route('seller.onboarding.create');

        return [
            'homeUrl' => route('home'),
            'catalogUrl' => route('catalog.index'),
            'valuationUrl' => route('valuation.index'),
            'sellUrl' => $sellUrl,
            'accountUrl' => $accountUrl,
            'loginUrl' => route('login'),
            'authUser' => $this->authUserPayload($accountUrl),
            'submitUrl' => route('valuation.store'),
            'csrfToken' => csrf_token(),
            'makes' => VehicleMake::query()->active()->with(['models' => fn ($query) => $query->active()->orderBy('name')])->orderBy('name')->get()->map(fn (VehicleMake $make) => [
                'id' => $make->id,
                'name' => $make->name,
                'models' => $make->models->values()->map(fn ($model) => [
                    'id' => $model->id,
                    'name' => $model->name,
                ]),
            ])->values(),
            'vehicleConfig' => [
                'conditions' => config('vehicle.conditions'),
                'bodyTypes' => config('vehicle.body_types'),
                'fuelTypes' => config('vehicle.fuel_types'),
                'transmissions' => config('vehicle.transmissions'),
                'drivetrains' => config('vehicle.drivetrains'),
                'cities' => ['San Jose', 'Escazu', 'Santa Ana', 'Alajuela', 'Heredia', 'Cartago', 'Guanacaste', 'Puntarenas', 'Limon'],
                'years' => range((int) date('Y') + 1, 1995),
            ],
            'aiEnabled' => $this->valuationSettings->valuationAiEnabled(),
            'aiConfigured' => $this->aiNarrator->configured(),
            'result' => null,
            'shareUrl' => null,
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'footerLinks' => [
                'termsUrl' => route('legal.terms'),
                'privacyUrl' => route('legal.privacy'),
                'cookiesUrl' => route('legal.cookies'),
            ],
        ];
    }

    private function authUserPayload(string $accountUrl): array
    {
        if (! auth()->check()) {
            return [
                'authenticated' => false,
            ];
        }

        $firstName = trim(strtok((string) auth()->user()->name, ' '));

        return [
            'authenticated' => true,
            'firstName' => $firstName !== '' ? $firstName : 'Cuenta',
            'dashboardUrl' => $accountUrl,
            'buyerUrl' => route('buyer.dashboard'),
        ];
    }

    private function mapValuation(VehicleValuation $valuation): array
    {
        $quote = $this->exchangeRateService->latest();
        $suggested = VehiclePricePresenter::present((float) $valuation->suggested_price, $valuation->currency, $quote);
        $min = VehiclePricePresenter::present((float) $valuation->min_price, $valuation->currency, $quote);
        $max = VehiclePricePresenter::present((float) $valuation->max_price, $valuation->currency, $quote);
        $snapshot = $valuation->input_snapshot ?? [];

        return [
            'title' => trim(((string) data_get($snapshot, 'vehicle_make_name')).' '.((string) data_get($snapshot, 'vehicle_model_name')).' '.((string) data_get($snapshot, 'year'))),
            'suggestedPrice' => $suggested['primary_formatted'],
            'suggestedPriceSecondary' => $suggested['secondary_formatted'],
            'minPrice' => $min['primary_formatted'],
            'minPriceSecondary' => $min['secondary_formatted'],
            'maxPrice' => $max['primary_formatted'],
            'maxPriceSecondary' => $max['secondary_formatted'],
            'confidenceScore' => (int) round((float) $valuation->confidence_score),
            'insights' => $valuation->market_insights ?? [],
            'aiSummary' => $valuation->ai_summary,
            'source' => $valuation->source,
            'snapshot' => $snapshot,
            'sellUrl' => route('seller.onboarding.create', array_filter([
                'vehicle_make_id' => data_get($snapshot, 'vehicle_make_id'),
                'vehicle_model_id' => data_get($snapshot, 'vehicle_model_id'),
                'year' => data_get($snapshot, 'year'),
                'condition' => data_get($snapshot, 'condition'),
                'body_type' => data_get($snapshot, 'body_type'),
                'fuel_type' => data_get($snapshot, 'fuel_type'),
                'transmission' => data_get($snapshot, 'transmission'),
                'drivetrain' => data_get($snapshot, 'drivetrain'),
                'mileage' => data_get($snapshot, 'mileage'),
                'engine_size' => data_get($snapshot, 'engine_size'),
                'city' => data_get($snapshot, 'city'),
                'price' => (int) round((float) $valuation->suggested_price),
            ], fn ($value) => filled($value))),
        ];
    }
}



