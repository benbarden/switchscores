<?php

namespace App\Providers\Domain\UserGamesCollection;

use Illuminate\Support\ServiceProvider;

use App\Domain\UserGamesCollection\DbQueries as UserGamesCollectionDbQueries;

class DbQueriesProvider extends ServiceProvider
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
        $this->app->bind('Domain\UserGamesCollection\DbQueries', function($app) {
            return new UserGamesCollectionDbQueries();
        });
    }
}
