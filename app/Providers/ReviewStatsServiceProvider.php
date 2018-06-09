<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ReviewStatsService;

class ReviewStatsServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ReviewStatsService', function($app) {
            return new ReviewStatsService();
        });
    }
}
