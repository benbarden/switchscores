<?php

namespace App\Providers\Domain\NewsCategory;

use Illuminate\Support\ServiceProvider;

use App\Domain\NewsCategory\Repository as NewsCategoryRepository;

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
        $this->app->bind('Domain\NewsCategory\Repository', function($app) {
            return new NewsCategoryRepository();
        });
    }
}
