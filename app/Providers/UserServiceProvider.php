<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserService;

class UserServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\UserService', function($app) {
            return new UserService();
        });
    }
}
