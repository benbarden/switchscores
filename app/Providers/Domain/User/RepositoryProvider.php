<?php

namespace App\Providers\Domain\User;

use Illuminate\Support\ServiceProvider;

use App\Domain\User\Repository as UserRepository;

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
        $this->app->bind('Domain\User\Repository', function($app) {
            return new UserRepository();
        });
    }
}
