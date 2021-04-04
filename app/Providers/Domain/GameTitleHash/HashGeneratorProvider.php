<?php

namespace App\Providers\Domain\GameTitleHash;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameTitleHash\HashGenerator as HashGeneratorRepository;

class HashGeneratorProvider extends ServiceProvider
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
        $this->app->bind('Domain\GameTitleHash\HashGenerator', function($app) {
            return new HashGeneratorRepository();
        });
    }
}
