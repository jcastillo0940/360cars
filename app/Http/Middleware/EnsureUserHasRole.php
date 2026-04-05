<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(Response::HTTP_UNAUTHORIZED, 'Unauthenticated.');
        }

        if (empty($roles) || $user->hasRole(...$roles)) {
            return $next($request);
        }

        abort(Response::HTTP_FORBIDDEN, 'No tienes permisos para acceder a este recurso.');
    }
}
