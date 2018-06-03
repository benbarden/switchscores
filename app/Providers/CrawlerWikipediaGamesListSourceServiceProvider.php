<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\CrawlerWikipediaGamesListSourceService;

class CrawlerWikipediaGamesListSourceServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\CrawlerWikipediaGamesListSourceService', function($app) {
            return new CrawlerWikipediaGamesListSourceService();
        });
    }
}
