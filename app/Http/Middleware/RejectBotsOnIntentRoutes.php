<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Returns 410 Gone for intent routes when accessed by search engine bots.
 *
 * Intent routes are for logged-in members only. When bots crawl them,
 * returning 410 tells search engines to remove these URLs from their index.
 * Regular unauthenticated users still get redirected to login as normal.
 */
class RejectBotsOnIntentRoutes
{
    public function handle(Request $request, Closure $next)
    {
        // If user is authenticated, proceed normally
        if ($request->user()) {
            return $next($request);
        }

        // Check if the request is from a known bot
        $userAgent = strtolower($request->userAgent() ?? '');
        $bots = ['googlebot', 'bingbot', 'yandex', 'baiduspider', 'duckduckbot', 'slurp', 'msnbot'];

        foreach ($bots as $bot) {
            if (str_contains($userAgent, $bot)) {
                // Tell search engines to remove this URL from their index
                abort(410, 'This page is no longer available.');
            }
        }

        // Regular unauthenticated users continue to auth middleware (redirect to login)
        return $next($request);
    }
}
