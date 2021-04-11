<?php

namespace App\Providers\Domain\GameTag;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameTag\Repository as GameTagRepository;

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
        $this->app->bind('Domain\GameTag\Repository', function($app) {
            return new GameTagRepository();
        });
    }
}
