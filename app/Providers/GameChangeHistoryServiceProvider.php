<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameChangeHistoryService;

class GameChangeHistoryServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GameChangeHistoryService', function($app) {
            return new GameChangeHistoryService();
        });
    }
}
