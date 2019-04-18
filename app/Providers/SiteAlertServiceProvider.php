<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\SiteAlertService;

class SiteAlertServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\SiteAlertService', function($app) {
            return new SiteAlertService();
        });
    }
}
