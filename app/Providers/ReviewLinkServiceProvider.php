<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ReviewLinkService;

class ReviewLinkServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ReviewLinkService', function($app) {
            return new ReviewLinkService();
        });
    }
}
