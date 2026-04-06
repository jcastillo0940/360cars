<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    public function __construct(private readonly ValuationSettingsService $valuationSettings)
    {
    }

    public function create()
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRoute(Auth::user()));
        }

        return view('auth.login', [
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'redirectTo' => request()->string('redirect')->toString(),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt([
            'email' => strtolower((string) $credentials['email']),
            'password' => $credentials['password'],
        ], true)) {
            return back()
                ->withErrors(['email' => 'Las credenciales proporcionadas no son validas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_seen_at' => now()])->save();

        $redirectTo = (string) $request->input('redirect');
        if ($redirectTo !== '' && str_starts_with($redirectTo, '/')) {
            return redirect()->to($redirectTo)->with('status', 'Bienvenido de nuevo.');
        }

        return redirect()->route($this->redirectRoute($request->user()))->with('status', 'Bienvenido de nuevo.');
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRoute(Auth::user()));
        }

        return view('auth.register', [
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
        ]);
    }

    public function registerStore(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower((string) $data['email']),
            'password' => $data['password'],
            'account_type' => $data['account_type'],
            'phone' => $data['phone'] ?? null,
            'whatsapp_phone' => $data['whatsapp_phone'] ?? null,
            'agency_name' => $data['agency_name'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'country_code' => strtoupper((string) ($data['country_code'] ?? 'CR')),
            'last_seen_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->redirectRoute($user))->with('status', 'Cuenta creada correctamente.');
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sesion cerrada correctamente.');
    }

    private function redirectRoute(User $user): string
    {
        if ($user->hasRole('admin')) {
            return 'admin.dashboard';
        }

        if ($user->hasRole('seller', 'dealer')) {
            return 'seller.dashboard';
        }

        return 'buyer.dashboard';
    }
}
