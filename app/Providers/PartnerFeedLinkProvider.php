<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\PartnerFeedLinkService;

class PartnerFeedLinkProvider extends ServiceProvider
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
        $this->app->bind('Services\PartnerFeedLinkService', function($app) {
            return new PartnerFeedLinkService();
        });
    }
}
