<?php

namespace App\Providers\Domain\GameStats;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameStats\Repository as GameStatsRepository;

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
        $this->app->bind('Domain\GameStats\Repository', function($app) {
            return new GameStatsRepository();
        });
    }
}
