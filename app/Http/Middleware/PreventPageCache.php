<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventPageCache
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $this->shouldDisableCache($request)) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT');
        $response->headers->set('Surrogate-Control', 'no-store');
        $response->headers->set('Vary', trim($response->headers->get('Vary').', Cookie, Authorization', ', '));

        return $response;
    }

    private function shouldDisableCache(Request $request): bool
    {
        if ($request->routeIs('login', 'login.store', 'logout', 'register', 'register.store')) {
            return true;
        }

        return $request->is('seller') || $request->is('seller/*')
            || $request->is('buyer') || $request->is('buyer/*')
            || $request->is('admin') || $request->is('admin/*');
    }
}
