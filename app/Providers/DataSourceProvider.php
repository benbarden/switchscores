<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\DataSourceService;

class DataSourceProvider extends ServiceProvider
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
        $this->app->bind('Services\DataSourceService', function($app) {
            return new DataSourceService();
        });
    }
}
