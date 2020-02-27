<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\DataSourceParsedService;

class DataSourceParsedProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Services\DataSourceParsedService', function($app) {
            return new DataSourceParsedService();
        });
    }
}
