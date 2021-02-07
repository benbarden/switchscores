<?php

namespace App\Providers\Domain\News;

use Illuminate\Support\ServiceProvider;

use App\Domain\News\Repository as NewsRepository;

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
        $this->app->bind('Domain\News\Repository', function($app) {
            return new NewsRepository();
        });
    }
}
