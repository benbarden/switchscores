<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ActivityFeedService;

class ActivityFeedProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('Services\ActivityFeedService', function($app) {
            return new ActivityFeedService();
        });
    }
}
