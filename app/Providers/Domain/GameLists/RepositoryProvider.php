<?php

namespace App\Providers\Domain\GameLists;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameLists\Repository as GameListsRepository;

class RepositoryProvider extends ServiceProvider
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
        $this->app->bind('Domain\GameLists\Repository', function($app) {
            return new GameListsRepository();
        });
    }
}
