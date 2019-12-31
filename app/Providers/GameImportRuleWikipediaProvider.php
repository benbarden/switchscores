<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameImportRuleWikipediaService;

class GameImportRuleWikipediaProvider extends ServiceProvider
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
        $this->app->bind('Services\GameImportRuleWikipediaService', function($app) {
            return new GameImportRuleWikipediaService();
        });
    }
}
