<?php

namespace App\Providers\Review;

use Illuminate\Support\ServiceProvider;

use App\Services\Review\StatsService;

class StatsServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\Review\StatsService', function($app) {
            return new StatsService();
        });
    }
}
