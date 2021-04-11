<?php

namespace App\Providers\Domain\Tag;

use Illuminate\Support\ServiceProvider;

use App\Domain\Tag\Repository as TagRepository;

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
        $this->app->bind('Domain\Tag\Repository', function($app) {
            return new TagRepository();
        });
    }
}
