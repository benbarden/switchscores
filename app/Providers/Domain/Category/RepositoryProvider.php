<?php

namespace App\Providers\Domain\Category;

use Illuminate\Support\ServiceProvider;

use App\Domain\Category\Repository as CategoryRepository;

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
        $this->app->bind('Domain\Category\Repository', function($app) {
            return new CategoryRepository();
        });
    }
}
