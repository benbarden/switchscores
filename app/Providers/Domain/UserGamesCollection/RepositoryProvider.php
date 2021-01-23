<?php

namespace App\Providers\Domain\UserGamesCollection;

use Illuminate\Support\ServiceProvider;

use App\Domain\UserGamesCollection\Repository as UserGamesCollectionRepository;

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
        $this->app->bind('Domain\UserGamesCollection\Repository', function($app) {
            return new UserGamesCollectionRepository();
        });
    }
}
