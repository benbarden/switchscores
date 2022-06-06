<?php

namespace App\Http\Middleware;

use Closure;

class ViewGlobals
{
    public function handle($request, Closure $next)
    {
        \View::share('siteEnv', env('APP_ENV'));
        \View::share('thisYear', date('Y'));

        return $next($request);
    }
}
