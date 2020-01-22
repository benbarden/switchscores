<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\QuickReviewService;

class QuickReviewServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\QuickReviewService', function($app) {
            return new QuickReviewService();
        });
    }
}
