<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameRankUpdateService;

class GameRankUpdateServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GameRankUpdateService', function($app) {
            return new GameRankUpdateService();
        });
    }
}
