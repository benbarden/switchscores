<?php

namespace App\Providers\Domain\GameCalendar;

use Illuminate\Support\ServiceProvider;

use App\Domain\GameCalendar\AllowedDates;

class AllowedDatesProvider extends ServiceProvider
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
        $this->app->bind('Domain\GameCalendar\AllowedDates', function($app) {
            return new AllowedDates();
        });
    }
}
