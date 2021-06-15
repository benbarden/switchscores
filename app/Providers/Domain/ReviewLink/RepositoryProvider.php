<?php

namespace App\Providers\Domain\ReviewLink;

use Illuminate\Support\ServiceProvider;

use App\Domain\ReviewLink\Repository as ReviewLinkRepository;

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
        $this->app->bind('Domain\ReviewLink\Repository', function($app) {
            return new ReviewLinkRepository();
        });
    }
}
