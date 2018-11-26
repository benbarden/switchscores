<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\TagService;

class TagServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\TagService', function($app) {
            return new TagService();
        });
    }
}
