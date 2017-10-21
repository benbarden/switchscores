<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ChartsRankingGlobalService;

class ChartsRankingGlobalServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ChartsRankingGlobalService', function($app) {
            return new ChartsRankingGlobalService();
        });
    }
}
