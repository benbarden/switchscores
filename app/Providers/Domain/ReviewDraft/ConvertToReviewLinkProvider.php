<?php

namespace App\Providers\Domain\ReviewDraft;

use Illuminate\Support\ServiceProvider;

use App\Domain\ReviewDraft\ConvertToReviewLink;

class ConvertToReviewLinkProvider extends ServiceProvider
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
        $this->app->bind('Domain\ReviewDraft\ConvertToReviewLink', function($app) {
            return new ConvertToReviewLink();
        });
    }
}
