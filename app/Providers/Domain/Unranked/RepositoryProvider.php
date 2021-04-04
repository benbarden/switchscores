<?php

namespace App\Providers\Domain\Unranked;

use Illuminate\Support\ServiceProvider;

use App\Domain\Unranked\Repository as UnrankedRepository;

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
        $this->app->bind('Domain\Unranked\Repository', function($app) {
            return new UnrankedRepository();
        });
    }
}
