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

        // Check if the request is from a known bot.
        // Match tokens explicitly rather than on a bare 'google' or 'bing' - some in-app and
        // mobile browser user agents contain those words, and a false positive would 410 a real
        // guest partway through the signup-intent flow these routes exist to serve.
        $userAgent = strtolower($request->userAgent() ?? '');
        $bots = [
            // Google crawls intent URLs under several agents, and only 'googlebot' was matched
            // before. GoogleOther fetches were getting a login redirect instead of the 410, which
            // filed those URLs under "Page with redirect" in GSC rather than removing them, and
            // Google-InspectionTool is what GSC's "Test Live URL" uses - so live-testing an intent
            // URL reported a redirect and not the 410 it actually serves to Googlebot.
            'googlebot', 'googleother', 'google-inspectiontool', 'google-extended',
            'adsbot-google', 'google-safety',
            'bingbot', 'bingpreview', 'msnbot',
            'yandex', 'baiduspider', 'duckduckbot', 'slurp',
        ];

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
