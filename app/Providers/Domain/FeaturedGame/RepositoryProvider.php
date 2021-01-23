<?php

namespace App\Providers\Domain\FeaturedGame;

use Illuminate\Support\ServiceProvider;

use App\Domain\FeaturedGame\Repository as FeaturedGameRepository;

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
        $this->app->bind('Domain\FeaturedGame\Repository', function($app) {
            return new FeaturedGameRepository();
        });
    }
}
