<?php

namespace App\Providers\Domain\GameTitleHash;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameTitleHash\Repository as GameTitleHashRepository;

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
        $this->app->bind('Domain\GameTitleHash\Repository', function($app) {
            return new GameTitleHashRepository();
        });
    }
}
