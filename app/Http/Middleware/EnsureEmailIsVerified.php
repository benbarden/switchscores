<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isEmailVerified()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Please verify your email to use this feature.'], 403);
            }

            return redirect()->route('members.index')
                ->with('error', 'Please verify your email to use this feature.');
        }

        return $next($request);
    }
}
