<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\MarioMakerLevelService;

class MarioMakerLevelServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\MarioMakerLevelService', function($app) {
            return new MarioMakerLevelService();
        });
    }
}
