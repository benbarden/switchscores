<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\NewsService;

class NewsServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\NewsService', function($app) {
            return new NewsService();
        });
    }
}
