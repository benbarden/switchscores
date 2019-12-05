<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\EshopEuropeAlertService;

class EshopEuropeAlertServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\EshopEuropeAlertService', function($app) {
            return new EshopEuropeAlertService();
        });
    }
}
