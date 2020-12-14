<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\DbEditGameService;

class DbEditGameProvider extends ServiceProvider
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
        $this->app->bind('Services\DbEditGameService', function($app) {
            return new DbEditGameService();
        });
    }
}
