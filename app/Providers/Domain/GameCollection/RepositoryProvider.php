<?php

namespace App\Providers\Domain\GameCollection;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameCollection\Repository as GameCollectionRepository;

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
        $this->app->bind('Domain\GameCollection\Repository', function($app) {
            return new GameCollectionRepository();
        });
    }
}
