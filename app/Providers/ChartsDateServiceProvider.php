<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\ChartsDateService;

class ChartsDateServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\ChartsDateService', function($app) {
            return new ChartsDateService();
        });
    }
}
