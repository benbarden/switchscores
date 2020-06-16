<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\CategoryService;

class CategoryServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\CategoryService', function($app) {
            return new CategoryService();
        });
    }
}
