<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\FeedItemReviewService;

class FeedItemReviewServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\FeedItemReviewService', function($app) {
            return new FeedItemReviewService();
        });
    }
}
