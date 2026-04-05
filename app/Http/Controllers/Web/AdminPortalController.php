<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleFeatureOption;
use App\Services\Currency\ExchangeRateService;
use App\Services\Valuation\ValuationSettingsService;
use App\Services\Valuation\VehicleValuationAiNarrator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPortalController extends Controller
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRateService,
        private readonly ValuationSettingsService $valuationSettings,
        private readonly VehicleValuationAiNarrator $valuationAiNarrator,
    ) {
    }

    public function index(Request $request)
    {
        $latestVehicles = Vehicle::query()->with(['owner', 'make', 'model'])->latest()->take(8)->get();
        $latestTransactions = Transaction::query()->with(['user', 'plan'])->latest()->take(8)->get();
        $latestUsers = User::query()->latest()->take(8)->get();
        $activeSubscriptions = Subscription::query()->with(['user', 'plan'])->where('status', 'active')->latest()->take(6)->get();
        $plans = Plan::query()->where('is_active', true)->orderBy('price')->get();
        $featureOptions = VehicleFeatureOption::query()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('portal.admin', [
            'gmv' => (float) Transaction::query()->where('status', 'paid')->sum('amount'),
            'newUsers' => User::query()->whereDate('created_at', today())->count(),
            'pendingModeration' => Vehicle::query()->whereIn('status', ['draft', 'paused'])->count(),
            'latestVehicles' => $latestVehicles,
            'latestTransactions' => $latestTransactions,
            'latestUsers' => $latestUsers,
            'activeSubscriptions' => $activeSubscriptions,
            'plans' => $plans,
            'vehicleCount' => Vehicle::query()->count(),
            'publishedVehicleCount' => Vehicle::query()->where('status', 'published')->count(),
            'leadCount' => (int) Vehicle::query()->sum('lead_count'),
            'paidTransactionsCount' => Transaction::query()->where('status', 'paid')->count(),
            'featureOptions' => $featureOptions,
            'exchangeQuote' => $this->exchangeRateService->latest(),
            'valuationAiEnabled' => $this->valuationSettings->valuationAiEnabled(),
            'valuationAiConfigured' => $this->valuationAiNarrator->configured(),
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
        ]);
    }

    public function refreshExchangeRate(): RedirectResponse
    {
        $quote = $this->exchangeRateService->refresh();

        if (! data_get($quote, 'usd_to_crc')) {
            return redirect()->to(route('admin.dashboard').'#exchange-rate')->with('status', 'No fue posible actualizar el tipo de cambio en este momento.');
        }

        return redirect()->to(route('admin.dashboard').'#exchange-rate')->with('status', 'Tipo de cambio actualizado correctamente.');
    }

    public function storeFeatureOption(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:160'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $slug = Str::slug($data['name']);

        $request->validate([
            'name' => [Rule::unique('vehicle_feature_options', 'name')],
        ]);

        VehicleFeatureOption::create([
            'name' => $data['name'],
            'slug' => $slug,
            'category' => Str::slug($data['category']),
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => true,
        ]);

        return redirect()->to(route('admin.dashboard').'#features')->with('status', 'Extra configurable creado correctamente.');
    }

    public function toggleFeatureOption(VehicleFeatureOption $featureOption): RedirectResponse
    {
        $featureOption->update([
            'is_active' => ! $featureOption->is_active,
        ]);

        return redirect()->to(route('admin.dashboard').'#features')->with('status', 'Estado del extra actualizado.');
    }

    public function updatePublicTheme(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'public_theme' => ['required', Rule::in(['light', 'dark'])],
        ]);

        $this->valuationSettings->put('frontend.public_theme', $data['public_theme'], 'string');

        return redirect()->to(route('admin.dashboard').'#public-theme')->with(
            'status',
            $data['public_theme'] === 'dark'
                ? 'Tema oscuro activado para el home publico.'
                : 'Tema claro activado para el home publico.'
        );
    }
}
