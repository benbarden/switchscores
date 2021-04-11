<?php

namespace App\Providers\Domain\TagCategory;

use Illuminate\Support\ServiceProvider;

use App\Domain\TagCategory\Repository as TagCategoryRepository;

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
        $this->app->bind('Domain\TagCategory\Repository', function($app) {
            return new TagCategoryRepository();
        });
    }
}
