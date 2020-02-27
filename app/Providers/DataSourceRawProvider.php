<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\DataSourceRawService;

class DataSourceRawProvider extends ServiceProvider
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
        $this->app->bind('Services\DataSourceRawService', function($app) {
            return new DataSourceRawService();
        });
    }
}
