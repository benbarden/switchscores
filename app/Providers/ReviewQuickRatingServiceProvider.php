<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ReviewQuickRatingService;

class ReviewQuickRatingServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ReviewQuickRatingService', function($app) {
            return new ReviewQuickRatingService();
        });
    }
}
