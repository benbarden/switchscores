<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GamePublisherService;

class GamePublisherServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Services\GamePublisherService', function($app) {
            return new GamePublisherService();
        });
    }
}
