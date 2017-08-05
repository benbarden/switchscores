<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\GenreService;

class GenreServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\GenreService', function($app) {
            return new GenreService();
        });
    }
}
