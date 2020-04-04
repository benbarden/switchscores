<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\DataSourceIgnoreService;

class DataSourceIgnoreProvider extends ServiceProvider
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
        $this->app->bind('Services\DataSourceIgnoreService', function($app) {
            return new DataSourceIgnoreService();
        });
    }
}
