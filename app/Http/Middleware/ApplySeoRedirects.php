<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ApplySeoRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') || ! Schema::hasTable('redirects')) {
            return $next($request);
        }

        $path = '/'.trim($request->path(), '/');
        if ($path === '//') {
            $path = '/';
        }

        $redirect = Cache::remember(
            'seo-redirect:'.$path,
            now()->addMinutes(10),
            fn () => Redirect::query()
                ->active()
                ->where('from_path', $path)
                ->first()
        );

        if ($redirect instanceof Redirect) {
            $redirect->increment('hit_count');

            return redirect()->to($redirect->to_url, $redirect->status_code);
        }

        return $next($request);
    }
}
