<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ReviewFeedItemTestService;

class ReviewFeedItemTestServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ReviewFeedItemTestService', function($app) {
            return new ReviewFeedItemTestService();
        });
    }
}
