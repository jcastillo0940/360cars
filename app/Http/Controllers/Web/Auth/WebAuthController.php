<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class WebAuthController extends Controller
{
    public function __construct(private readonly ValuationSettingsService $valuationSettings)
    {
    }

    public function create()
    {
        Log::info('auth.login_page', [
            'auth_check' => Auth::check(),
            'user_id' => Auth::id(),
            'redirect_to' => request()->string('redirect')->toString(),
            'session_id' => request()->hasSession() ? request()->session()->getId() : null,
        ]);

        if (Auth::check()) {
            $route = $this->redirectRoute(Auth::user());

            Log::info('auth.login_page.redirect_authenticated', [
                'user_id' => Auth::id(),
                'route' => $route,
            ]);

            return redirect()->route($route);
        }

        return view('auth.login', [
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'redirectTo' => request()->string('redirect')->toString(),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();
        $email = strtolower((string) $credentials['email']);

        Log::info('auth.login_attempt.start', [
            'email' => $email,
            'redirect' => (string) $request->input('redirect'),
            'session_id_before' => $request->session()->getId(),
            'session_token_prefix_before' => is_string($request->session()->token()) ? substr((string) $request->session()->token(), 0, 12) : null,
            'has_session_cookie' => $request->cookies->has((string) config('session.cookie')),
            'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
        ]);

        $authenticated = Auth::attempt([
            'email' => $email,
            'password' => $credentials['password'],
        ], true);

        Log::info('auth.login_attempt.result', [
            'email' => $email,
            'authenticated' => $authenticated,
            'auth_check_after_attempt' => Auth::check(),
            'user_id_after_attempt' => Auth::id(),
            'session_id_after_attempt' => $request->session()->getId(),
        ]);

        if (! $authenticated) {
            return back()
                ->withErrors(['email' => 'Las credenciales proporcionadas no son validas.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_seen_at' => now()])->save();

        $redirectTo = (string) $request->input('redirect');

        Log::info('auth.login_attempt.authenticated', [
            'user_id' => $request->user()?->id,
            'account_type' => $request->user()?->account_type,
            'session_id_regenerated' => $request->session()->getId(),
            'session_token_prefix_after_regenerate' => is_string($request->session()->token()) ? substr((string) $request->session()->token(), 0, 12) : null,
            'redirect_input' => $redirectTo,
        ]);

        if ($redirectTo !== '' && str_starts_with($redirectTo, '/')) {
            Log::info('auth.login_attempt.redirecting_custom', [
                'user_id' => $request->user()?->id,
                'redirect_to' => $redirectTo,
            ]);

            return redirect()->to($redirectTo)->with('status', 'Bienvenido de nuevo.');
        }

        $route = $this->redirectRoute($request->user());

        Log::info('auth.login_attempt.redirecting_default', [
            'user_id' => $request->user()?->id,
            'route' => $route,
        ]);

        return redirect()->route($route)->with('status', 'Bienvenido de nuevo.');
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRoute(Auth::user()));
        }

        return view('auth.register', [
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
            'countryOptions' => User::countryOptions(),
        ]);
    }

    public function registerStore(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => strtolower((string) $data['email']),
            'password' => $data['password'],
            'account_type' => 'seller',
            'phone' => $data['phone'] ?? null,
            'whatsapp_phone' => $data['phone'] ?? null,
            'country_code' => strtoupper((string) ($data['country_code'] ?? 'CR')),
            'last_seen_at' => now(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route($this->redirectRoute($user))->with('status', 'Cuenta creada correctamente.');
    }

    public function forgotPassword()
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRoute(Auth::user()));
        }

        return view('auth.forgot-password', [
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
        ]);
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink([
            'email' => strtolower((string) $credentials['email']),
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors([
                'email' => __($status),
            ])->onlyInput('email');
        }

        return back()->with('status', 'Te enviamos un enlace para rest?blecer tu contraseña.');
    }

    public function resetPassword(string $token, Request $request)
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectRoute(Auth::user()));
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
            'publicTheme' => (string) $this->valuationSettings->get('frontend.public_theme', 'light'),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)->letters()->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            [
                'email' => strtolower((string) $data['email']),
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors([
                'email' => __($status),
            ])->withInput($request->except('password', 'password_confirmation'));
        }

        return redirect()->route('login')->with('status', 'Tu contraseña fue actualizada correctamente.');
    }

    public function destroy(): RedirectResponse
    {
        Log::info('auth.logout', [
            'user_id' => Auth::id(),
            'session_id' => request()->hasSession() ? request()->session()->getId() : null,
        ]);

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
