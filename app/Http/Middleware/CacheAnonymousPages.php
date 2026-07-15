<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CacheAnonymousPages
{
    /**
     * Pages that must never be cached, even though they sit under a long-lived prefix.
     * The Cloudflare cache rule matches `/games/` with a "starts with", so anything under
     * it is edge-cached unless the origin says otherwise.
     */
    const NEVER_CACHE = [
        // Whole point is to differ per request; a cached response served every visitor the
        // same "random" game until the edge TTL expired.
        '/games/random',
    ];

    /**
     * Pages under a 24-hour prefix whose content moves too fast for it, so they fall back
     * to the 1-hour default.
     */
    const SHORT_LIVED = [
        // Discounts expire; a day-old edge cache shows prices that no longer exist.
        '/games/on-sale',
    ];

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $path = $request->getPathInfo();

        if (in_array($path, self::NEVER_CACHE, true)) {
            $response->headers->set('Cache-Control', 'no-store, private');

            return $response;
        }

        if ($request->isMethodSafe()) {
            // Public cache headers
            /*
             * s-maxage=3600: CF edge caching for 1 hour
             * max-age=300: Browser caching for 5 minutes
             * stale-while-revalidate=600: CF serves stale content for 10 mins even when cache expires
             */
            // Switch 1 games are at /games/{id}/{slug}, Switch 2 games are console-prefixed at
            // /switch-2/games/{id}/{slug}, and the browse lists replaced the old /c/ URLs — all
            // need matching explicitly or they fall through to the 1-hour default below.
            // NB: these prefixes must also exist in the Cloudflare cache rule, or the header is
            // decorative and the page is never edge-cached at all.
            if (!in_array($path, self::SHORT_LIVED, true)
                && preg_match('#^/(games/|switch-2/games/|browse/|partners/games-company/)#', $path)) {
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