<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\NewsCategoryService;

class NewsCategoryServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\NewsCategoryService', function($app) {
            return new NewsCategoryService();
        });
    }
}
