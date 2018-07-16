<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RegionCode
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->region) {
            $regionCode = Auth::user()->region;
            //\View::share('auth_user', Auth::user());
            //\View::share('auth_id', Auth::id());
            //\View::share('user', Auth::user());
            //\View::share('uid', Auth::id());
        } else {
            $regionCode = 'eu';
        }
        \View::share('region', $regionCode);

        $request->attributes->add(['regionCode' => $regionCode]);

        return $next($request);
    }
}