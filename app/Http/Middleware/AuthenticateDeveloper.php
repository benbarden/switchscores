<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AuthenticateDeveloper
{
    public function handle($request, Closure $next)
    {
        $canAccess = false;

        if (Auth::check()) {

            if (Auth::user()->isDeveloper()) {
                $canAccess = true;
            }

        }

        if (!$canAccess) {
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