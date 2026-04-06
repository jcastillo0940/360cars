<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogAuthDebug
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldLog($request)) {
            return $next($request);
        }

        $context = [
            'method' => $request->method(),
            'path' => $request->path(),
            'full_url' => $request->fullUrl(),
            'route' => optional($request->route())->getName(),
            'host' => $request->getHost(),
            'scheme' => $request->getScheme(),
            'is_secure' => $request->isSecure(),
            'ip' => $request->ip(),
            'auth_check_before' => Auth::check(),
            'user_id_before' => Auth::id(),
            'session_started_before' => $request->hasSession(),
            'session_id_before' => $request->hasSession() ? $request->session()->getId() : null,
            'session_token_prefix_before' => $request->hasSession() && is_string($request->session()->token()) ? substr((string) $request->session()->token(), 0, 12) : null,
            'cookies_present' => array_keys($request->cookies->all()),
            'has_session_cookie' => $request->cookies->has((string) config('session.cookie')),
            'has_xsrf_cookie' => $request->cookies->has('XSRF-TOKEN'),
            'x_forwarded_proto' => $request->headers->get('x-forwarded-proto'),
            'x_forwarded_host' => $request->headers->get('x-forwarded-host'),
            'x_forwarded_port' => $request->headers->get('x-forwarded-port'),
            'referer' => $request->headers->get('referer'),
            'origin' => $request->headers->get('origin'),
            'sec_fetch_site' => $request->headers->get('sec-fetch-site'),
        ];

        if ($request->isMethod('post') && $request->routeIs('login.store')) {
            $context['posted_email'] = strtolower((string) $request->input('email'));
            $context['has_password'] = filled($request->input('password'));
            $context['posted_token_prefix'] = is_string($request->input('_token')) ? substr((string) $request->input('_token'), 0, 12) : null;
        }

        Log::info('auth.debug.request', $context);

        /** @var Response $response */
        $response = $next($request);

        Log::info('auth.debug.response', [
            'method' => $request->method(),
            'path' => $request->path(),
            'route' => optional($request->route())->getName(),
            'status' => $response->getStatusCode(),
            'location' => $response->headers->get('Location'),
            'auth_check_after' => Auth::check(),
            'user_id_after' => Auth::id(),
            'session_id_after' => $request->hasSession() ? $request->session()->getId() : null,
            'session_token_prefix_after' => $request->hasSession() && is_string($request->session()->token()) ? substr((string) $request->session()->token(), 0, 12) : null,
            'set_cookie_headers' => $response->headers->getCookies() ? array_map(static fn ($cookie) => [
                'name' => $cookie->getName(),
                'domain' => $cookie->getDomain(),
                'path' => $cookie->getPath(),
                'secure' => $cookie->isSecure(),
                'same_site' => $cookie->getSameSite(),
            ], $response->headers->getCookies()) : [],
        ]);

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if ($request->routeIs('login', 'login.store', 'logout')) {
            return true;
        }

        return $request->is('seller') || $request->is('seller/*') || $request->is('buyer') || $request->is('buyer/*') || $request->is('admin') || $request->is('admin/*');
    }
}
