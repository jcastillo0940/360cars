<?php

use App\Http\Middleware\ApplySecurityHeaders;
use App\Http\Middleware\ApplySeoRedirects;
use App\Http\Middleware\BlockSuspiciousRequests;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LogAuthDebug;
use App\Http\Middleware\LoggablePreventRequestForgery;
use App\Http\Middleware\PreventPageCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(BlockSuspiciousRequests::class);
        $middleware->append(ApplySeoRedirects::class);
        $middleware->append(ApplySecurityHeaders::class);
        $middleware->statefulApi();
        $middleware->trustProxies(at: '*');
        $middleware->web(prepend: [
            PreventPageCache::class,
            LogAuthDebug::class,
        ], replace: [
            PreventRequestForgery::class => LoggablePreventRequestForgery::class,
        ]);
        $middleware->alias([
            'active' => EnsureUserIsActive::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
