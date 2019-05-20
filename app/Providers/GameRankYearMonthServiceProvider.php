<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameRankYearMonthService;

class GameRankYearMonthServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GameRankYearMonthService', function($app) {
            return new GameRankYearMonthService();
        });
    }
}
