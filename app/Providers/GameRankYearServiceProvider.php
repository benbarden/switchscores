<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameRankYearService;

class GameRankYearServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GameRankYearService', function($app) {
            return new GameRankYearService();
        });
    }
}
