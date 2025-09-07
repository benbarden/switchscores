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
             * stale-while-revalidate=600: CF serves stale content for 10 mins even when cache expires
             */
            if (preg_match('#^/(games/|partners/games-company/)#', $request->getPathInfo())) {
                // Extend cache to 24 hours for large groups of pages
                $response->headers->set(
                    'Cache-Control',
                    'public, s-maxage=86400, max-age=300, stale-while-revalidate=600, stale-if-error=600'
                );
            } else {
                $response->headers->set(
                    'Cache-Control',
                    'public, s-maxage=3600, max-age=300, stale-while-revalidate=600, stale-if-error=600'
                );
            }

            // Strip cookies if no session cookie in request
            if (!$request->hasCookie('laravel_session')) {
                $response->headers->remove('Set-Cookie');
            }
        }

        return $response;
    }
}