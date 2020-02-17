<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ReviewFeedImportService;

class ReviewFeedImportServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ReviewFeedImportService', function($app) {
            return new ReviewFeedImportService();
        });
    }
}
