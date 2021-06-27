<?php

namespace App\Providers\Domain\IntegrityCheck;

use Illuminate\Support\ServiceProvider;

use App\Domain\IntegrityCheck\Repository as IntegrityCheckRepository;

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
        $this->app->bind('Domain\IntegrityCheck\Repository', function($app) {
            return new IntegrityCheckRepository();
        });
    }
}
