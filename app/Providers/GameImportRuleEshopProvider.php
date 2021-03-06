<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameImportRuleEshopService;

class GameImportRuleEshopProvider extends ServiceProvider
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
        $this->app->bind('Services\GameImportRuleEshopService', function($app) {
            return new GameImportRuleEshopService();
        });
    }
}
