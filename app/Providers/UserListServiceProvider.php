<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\UserListService;

class UserListServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\UserListService', function($app) {
            return new UserListService();
        });
    }
}
