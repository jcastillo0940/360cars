<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Log;

class LoggablePreventRequestForgery extends PreventRequestForgery
{
    public function __construct(Application $app, Encrypter $encrypter)
    {
        parent::__construct($app, $encrypter);
    }

    public function handle($request, Closure $next)
    {
        if (! app()->environment(['local', 'staging']) && ! config('app.debug')) {
            return parent::handle($request, $next);
        }

        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $exception) {
            Log::error('csrf.token_mismatch', [
                'method' => $request->method(),
                'path' => $request->path(),
                'full_url' => $request->fullUrl(),
                'route' => optional($request->route())->getName(),
                'host' => $request->getHost(),
                'scheme' => $request->getScheme(),
                'is_secure' => $request->isSecure(),
                'ip' => $request->ip(),
                'user_id' => optional($request->user())->id,
                'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
                'has_session_cookie' => $request->cookies->has((string) config('session.cookie')),
                'referer' => $request->headers->get('referer'),
                'origin' => $request->headers->get('origin'),
                'x_forwarded_proto' => $request->headers->get('x-forwarded-proto'),
                'x_forwarded_host' => $request->headers->get('x-forwarded-host'),
                'x_forwarded_port' => $request->headers->get('x-forwarded-port'),
                'sec_fetch_site' => $request->headers->get('sec-fetch-site'),
                'session_config' => [
                    'driver' => config('session.driver'),
                    'domain' => config('session.domain'),
                    'path' => config('session.path'),
                    'secure' => config('session.secure'),
                    'same_site' => config('session.same_site'),
                    'cookie' => config('session.cookie'),
                ],
            ]);

            throw $exception;
        }
    }
}
