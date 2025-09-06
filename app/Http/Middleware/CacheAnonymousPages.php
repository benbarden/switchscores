<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheAnonymousPages
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($request->isMethodSafe()) {
            // Public cache headers
            $response->headers->set(
                'Cache-Control',
                'public, s-maxage=600, max-age=60, stale-while-revalidate=120, stale-if-error=600'
            );

            // Strip cookies if no session cookie in request
            if (!$request->hasCookie('laravel_session')) {
                $response->headers->remove('Set-Cookie');
            }
        }

        return $response;
    }
}