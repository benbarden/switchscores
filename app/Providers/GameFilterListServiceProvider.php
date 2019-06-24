<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameFilterListService;

class GameFilterListServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GameFilterListService', function($app) {
            return new GameFilterListService();
        });
    }
}
