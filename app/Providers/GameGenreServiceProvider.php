<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GameGenreService;

class GameGenreServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GameGenreService', function($app) {
            return new GameGenreService();
        });
    }
}
