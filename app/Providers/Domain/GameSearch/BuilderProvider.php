<?php

namespace App\Providers\Domain\GameSearch;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameSearch\Builder as GameSearchBuilder;

class BuilderProvider extends ServiceProvider
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
        $this->app->bind('Domain\GameSearch\Builder', function($app) {
            return new GameSearchBuilder();
        });
    }
}
