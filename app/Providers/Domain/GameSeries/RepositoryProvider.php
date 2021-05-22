<?php

namespace App\Providers\Domain\GameSeries;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameSeries\Repository as GameSeriesRepository;

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
        $this->app->bind('Domain\GameSeries\Repository', function($app) {
            return new GameSeriesRepository();
        });
    }
}
