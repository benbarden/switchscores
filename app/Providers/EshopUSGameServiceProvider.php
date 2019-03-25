<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\EshopUSGameService;

class EshopUSGameServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\EshopUSGameService', function($app) {
            return new EshopUSGameService();
        });
    }
}
