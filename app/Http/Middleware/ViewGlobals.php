<?php

namespace App\Http\Middleware;

use Closure;

use App\Models\Console;
use App\Domain\Console\Repository;

use Illuminate\Support\Facades\View;

class ViewGlobals
{
    public function handle($request, Closure $next)
    {
        View::share('siteEnv', env('APP_ENV'));
        View::share('thisYear', date('Y'));

        $releaseYears = [];
        for ($year = 2017; $year <= date('Y'); $year++) {
            array_unshift($releaseYears, $year);
        }

        View::share('releaseYears', $releaseYears);

        $repoConsole = new Repository();
        $consoleSwitch1 = $repoConsole->find(Console::ID_SWITCH_1);
        $consoleSwitch2 = $repoConsole->find(Console::ID_SWITCH_2);
        View::share('consoleSwitch1', $consoleSwitch1);
        View::share('consoleSwitch2', $consoleSwitch2);

        return $next($request);
    }
}
