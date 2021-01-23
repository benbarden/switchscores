<?php

namespace App\Providers\Domain\ViewHelper;

use Illuminate\Support\ServiceProvider;

use App\Services\ViewHelper\Bindings as ViewHelperBindings;

class BindingsProvider extends ServiceProvider
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
        $this->app->bind('Domain\ViewHelper\Bindings', function($app) {
            return new ViewHelperBindings();
        });
    }
}
