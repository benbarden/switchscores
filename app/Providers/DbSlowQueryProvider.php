<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\App;

class DbSlowQueryProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if (!env('LOG_SLOW_QUERIES', false)) {
            return;
        }

        DB::listen(function ($query) {
            $queryTrigger = env('LOG_SLOW_QUERY_TRIGGER');
            if ($query->time > $queryTrigger) {
                // Grab route info
                $route = \Route::current();
                $controllerAction = optional($route)->getActionName(); // e.g., App\Http\Controllers\GameController@index
                $routeUri = optional($route)->uri();                   // e.g., games/{id}
                $routeName = optional($route)->getName();              // e.g., games.show
                $method = \Request::method();                           // GET, POST, etc.
                $fullUrl = \Request::fullUrl();

                // Trim controller action to something friendlier
                if (str_contains($controllerAction, '@') !== false) {
                    [$controller, $action] = explode('@', $controllerAction);
                    $controller = class_basename($controller); // Remove namespace
                } else {
                    $controller = $controllerAction;
                    $action = '';
                }

                Log::channel('slow_queries')->debug('[Slow DB Query]', [
                    'time_ms'     => $query->time,
                    'sql'         => $query->sql,
                    'bindings'    => $query->bindings,
                    'route_uri'   => $routeUri,
                    'route_name'  => $routeName,
                    'controller'  => $controller ?? 'N/A',
                    'action'      => $action ?? 'N/A',
                    'method'      => $method,
                    'full_url'    => $fullUrl,
                ]);
            }
        });
    }
}
