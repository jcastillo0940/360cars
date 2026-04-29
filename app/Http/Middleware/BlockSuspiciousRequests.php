<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousRequests
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = (string) $request->ip();
        $userAgent = strtolower((string) $request->userAgent());
        $allowedIps = config('security.allowed_ips', []);
        $blockedIps = config('security.blocked_ips', []);
        $blockedAgents = config('security.blocked_user_agents', []);

        if ($allowedIps !== [] && ! in_array($ip, $allowedIps, true)) {
            abort(403, 'Access denied.');
        }

        if ($blockedIps !== [] && in_array($ip, $blockedIps, true)) {
            abort(403, 'Access denied.');
        }

        foreach ($blockedAgents as $needle) {
            if ($needle !== '' && str_contains($userAgent, strtolower((string) $needle))) {
                abort(403, 'Access denied.');
            }
        }

        return $next($request);
    }
}
