<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\ApiRequestLog;

/**
 * Records API requests to the api_request_log table.
 *
 * Currently scoped to the public (unauthenticated) V1 game endpoints so we can
 * see when legacy V1 is being hit, and by whom, ahead of removing it. May be
 * expanded to other API routes later - pass the version marker as a parameter,
 * e.g. 'log.api:V1'. See docs/api-v1-deprecation-plan.md.
 *
 * Logging happens in terminate() so it runs after the response is sent and adds
 * no latency for the caller.
 */
class LogApiRequest
{
    public function handle($request, Closure $next, $apiVersion = null)
    {
        $request->attributes->set('api_log_version', $apiVersion);
        $request->attributes->set('api_log_start', microtime(true));

        return $next($request);
    }

    public function terminate($request, $response)
    {
        $start = $request->attributes->get('api_log_start');
        $durationMs = $start ? (int) round((microtime(true) - $start) * 1000) : null;

        $tokenId = null;
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken')) {
            $token = $user->currentAccessToken();
            if ($token) {
                $tokenId = $token->id ?? null;
            }
        }

        ApiRequestLog::create([
            'api_version' => $request->attributes->get('api_log_version'),
            'method' => $request->getMethod(),
            'path' => $request->path(),
            'status_code' => $response->getStatusCode(),
            'token_id' => $tokenId,
            'ip' => $request->ip(),
            'duration_ms' => $durationMs,
        ]);
    }
}
