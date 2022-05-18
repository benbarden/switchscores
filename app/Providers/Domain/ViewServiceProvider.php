<?php

namespace App\Providers\Domain;

use Illuminate\Support\ServiceProvider;

use App\Domain\ViewBreadcrumbs\MainSite as MainSiteBreadcrumbs;
use App\Domain\ViewBreadcrumbs\Staff as StaffBreadcrumbs;

use App\Domain\ViewBindings\MainSite as MainSiteBindings;
use App\Domain\ViewBindings\Staff as StaffBindings;

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
        $this->app->singleton('View/Breadcrumbs/Staff', function($app) {
            return new StaffBreadcrumbs();
        });
        $this->app->singleton('View/Bindings/MainSite', function($app) {
            return new MainSiteBindings();
        });
        $this->app->singleton('View/Bindings/Staff', function($app) {
            return new StaffBindings();
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
