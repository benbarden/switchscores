<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameDescriptionService;

class GameDescriptionProvider extends ServiceProvider
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
        $this->app->bind('Services\GameDescriptionService', function($app) {
            return new GameDescriptionService();
        });
    }
}
