<?php

namespace App\Providers\Domain\Game;

use Illuminate\Support\ServiceProvider;

use App\Domain\Game\Repository as GameRepository;

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
        $this->app->bind('Domain\Game\Repository', function($app) {
            return new GameRepository();
        });
    }
}
