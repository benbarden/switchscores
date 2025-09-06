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
            /*
             * s-maxage=3600: CF edge caching for 1 hour
             * max-age=300: Browser caching for 5 minutes
             */
            $response->headers->set(
                'Cache-Control',
                'public, s-maxage=3600, max-age=300, stale-while-revalidate=120, stale-if-error=600'
            );

            // Strip cookies if no session cookie in request
            if (!$request->hasCookie('laravel_session')) {
                $response->headers->remove('Set-Cookie');
            }
        }

        return $response;
    }
}