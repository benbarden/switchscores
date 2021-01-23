<?php

namespace App\Providers\Domain\ViewHelper;

use Illuminate\Support\ServiceProvider;

use App\Services\ViewHelper\StaffBreadcrumbs as ViewHelperStaffBreadcrumbs;

class StaffBreadcrumbsProvider extends ServiceProvider
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
        $this->app->bind('Domain\ViewHelper\StaffBreadcrumbs', function($app) {
            return new ViewHelperStaffBreadcrumbs();
        });
    }
}
