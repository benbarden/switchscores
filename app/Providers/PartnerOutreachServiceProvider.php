<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Services\PartnerOutreachService;

class PartnerOutreachServiceProvider extends ServiceProvider
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
        $this->app->bind('Services\PartnerOutreachService', function($app) {
            return new PartnerOutreachService();
        });
    }
}
