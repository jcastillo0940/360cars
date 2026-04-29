<?php

namespace App\Providers;

use App\Models\NewsPost;
use App\Models\Vehicle;
use App\Observers\NewsPostObserver;
use App\Observers\VehicleObserver;
use App\Services\Valuation\ValuationSettingsService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        $this->applyRuntimeSecurityOverrides();
        RateLimiter::for('auth-login', function (Request $request): array {
            $email = Str::lower((string) $request->input('email'));

            return [
                Limit::perMinute(5)->by($email.'|'.$request->ip()),
                Limit::perMinute(20)->by($request->ip()),
            ];
        });
        RateLimiter::for('auth-register', fn (Request $request): array => [
            Limit::perMinutes(10, 3)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinutes(10, 8)->by($request->ip()),
        ]);
        RateLimiter::for('password-reset-link', fn (Request $request): array => [
            Limit::perMinutes(10, 3)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinutes(10, 6)->by($request->ip()),
        ]);
        RateLimiter::for('password-reset-submit', fn (Request $request): array => [
            Limit::perMinutes(10, 5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinutes(10, 10)->by($request->ip()),
        ]);
        RateLimiter::for('seller-onboarding', fn (Request $request): array => [
            Limit::perMinutes(15, 4)->by($request->ip()),
        ]);
        RateLimiter::for('valuation-submit', fn (Request $request): array => [
            Limit::perMinute(8)->by($request->ip()),
        ]);

        Vehicle::observe(VehicleObserver::class);
        NewsPost::observe(NewsPostObserver::class);
    }

    private function applyRuntimeSecurityOverrides(): void
    {
        $settings = app(ValuationSettingsService::class);
        $safeGet = function (string $key, mixed $default = null) use ($settings): mixed {
            try {
                return $settings->get($key, $default);
            } catch (Throwable) {
                return $default;
            }
        };

        config([
            'honeypot.enabled' => (bool) $safeGet('security.honeypot.enabled', config('honeypot.enabled')),
            'honeypot.randomize_name_field_name' => (bool) $safeGet('security.honeypot.randomize_name_field_name', config('honeypot.randomize_name_field_name')),
            'honeypot.amount_of_seconds' => (int) $safeGet('security.honeypot.amount_of_seconds', config('honeypot.amount_of_seconds')),
            'clamav.preferred_socket' => (string) $safeGet('security.clamav.preferred_socket', config('clamav.preferred_socket')),
            'clamav.unix_socket' => (string) $safeGet('security.clamav.unix_socket', config('clamav.unix_socket')),
            'clamav.tcp_socket' => (string) $safeGet('security.clamav.tcp_socket', config('clamav.tcp_socket')),
            'clamav.socket_connect_timeout' => (int) $safeGet('security.clamav.socket_connect_timeout', config('clamav.socket_connect_timeout')),
            'clamav.socket_read_timeout' => (int) $safeGet('security.clamav.socket_read_timeout', config('clamav.socket_read_timeout')),
            'clamav.client_exceptions' => (bool) $safeGet('security.clamav.client_exceptions', config('clamav.client_exceptions')),
            'clamav.skip_validation' => (bool) $safeGet('security.clamav.skip_validation', config('clamav.skip_validation')),
            'security.blocked_ips' => array_values($safeGet('security.request_filters.blocked_ips', config('security.blocked_ips', [])) ?: []),
            'security.allowed_ips' => array_values($safeGet('security.request_filters.allowed_ips', config('security.allowed_ips', [])) ?: []),
            'security.blocked_user_agents' => array_values($safeGet('security.request_filters.blocked_user_agents', config('security.blocked_user_agents', [])) ?: []),
        ]);
    }
}
