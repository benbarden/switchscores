<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserGamesCollectionService;

class UserGamesCollectionServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\UserGamesCollectionService', function($app) {
            return new UserGamesCollectionService();
        });
    }
}
