<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\PartnerReviewService;

class PartnerReviewServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\PartnerReviewService', function($app) {
            return new PartnerReviewService();
        });
    }
}
