<?php

namespace App\Providers\Domain\IntegrityCheckResult;

use Illuminate\Support\ServiceProvider;

use App\Domain\IntegrityCheckResult\Repository as IntegrityCheckResultRepository;

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
        $this->app->bind('Domain\IntegrityCheckResult\Repository', function($app) {
            return new IntegrityCheckResultRepository();
        });
    }
}
