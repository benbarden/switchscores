<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\PublisherService;

class PublisherServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\PublisherService', function($app) {
            return new PublisherService();
        });
    }
}
