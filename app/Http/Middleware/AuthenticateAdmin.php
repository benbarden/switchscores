<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateAdmin
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            // OK
        } else {
            // Not authorised
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorised.', 401);
            } else {
                return abort(401);
            }
        }

        return $next($request);
    }
}