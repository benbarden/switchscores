<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ChartsRankingUsService;

class ChartsRankingUsServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ChartsRankingUsService', function($app) {
            return new ChartsRankingUsService();
        });
    }
}
