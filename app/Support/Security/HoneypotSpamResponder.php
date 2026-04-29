<?php

namespace App\Support\Security;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Honeypot\SpamResponder\SpamResponder;

class HoneypotSpamResponder implements SpamResponder
{
    public function respond(Request $request, Closure $next): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => 'La solicitud no pudo validarse. Intenta nuevamente.',
            ], 422);
        }

        return back()
            ->withErrors(['form' => 'La solicitud no pudo validarse. Intenta nuevamente.'])
            ->withInput();
    }
}
