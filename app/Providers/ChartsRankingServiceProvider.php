<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ChartsRankingService;

class ChartsRankingServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ChartsRankingService', function($app) {
            return new ChartsRankingService();
        });
    }
}
