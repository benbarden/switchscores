<?php

namespace App\Providers\Domain\Partner;

use Illuminate\Support\ServiceProvider;

use App\Domain\Partner\Repository as PartnerRepository;

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
        $this->app->bind('Domain\Partner\Repository', function($app) {
            return new PartnerRepository();
        });
    }
}
