<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\ServiceContainer;

class ServiceContainerLoader
{
    public function handle($request, Closure $next)
    {
        $serviceContainer = new ServiceContainer();

        $request->attributes->add(['serviceContainer' => $serviceContainer]);

        return $next($request);
    }
}