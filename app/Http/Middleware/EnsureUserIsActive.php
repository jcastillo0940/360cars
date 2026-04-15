<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_active) {
            return $next($request);
        }

        $request->user()->currentAccessToken()?->delete();

        if (! $request->expectsJson()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Tu cuenta esta desactivada. Contacta al administrador.',
            ]);
        }

        return response()->json([
            'message' => 'Tu cuenta esta desactivada. Contacta al administrador.',
        ], Response::HTTP_FORBIDDEN);
    }
}
