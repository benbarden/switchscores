<?php

namespace App\Http\Middleware;

use Closure;

class ViewGlobals
{
    public function handle($request, Closure $next)
    {
        \View::share('siteEnv', env('APP_ENV'));
        \View::share('thisYear', date('Y'));

        $releaseYears = [];
        for ($year = 2017; $year <= date('Y'); $year++) {
            array_unshift($releaseYears, $year);
        }

        \View::share('releaseYears', $releaseYears);

        return $next($request);
    }
}
