<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ReviewUserService;

class ReviewUserServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ReviewUserService', function($app) {
            return new ReviewUserService();
        });
    }
}
