<?php

namespace App\Providers\Domain\GameLists;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameLists\DbQueries as GameListsDbQueries;

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
        $this->app->bind('Domain\GameLists\DbQueries', function($app) {
            return new GameListsDbQueries();
        });
    }
}
