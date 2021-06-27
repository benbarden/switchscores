<?php

namespace App\Providers\Domain\IntegrityCheck;

use Illuminate\Support\ServiceProvider;

use App\Domain\IntegrityCheck\UpdateResults as IntegrityCheckUpdateResults;

class UpdateResultsProvider extends ServiceProvider
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
        $this->app->bind('Domain\IntegrityCheck\UpdateResults', function($app) {
            return new IntegrityCheckUpdateResults();
        });
    }
}
