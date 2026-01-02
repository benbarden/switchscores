<?php

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;

use App\Domain\ViewBreadcrumbs\MainSite as MainSiteBreadcrumbs;

use App\Domain\ViewBindings\MainSite as MainSiteBindings;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('View/Breadcrumbs/MainSite', function($app) {
            return new MainSiteBreadcrumbs();
        });

        $this->app->singleton('View/Bindings/MainSite', function($app) {
            return new MainSiteBindings();
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
