<?php

namespace App\Providers\Domain\Campaign;

use Illuminate\Support\ServiceProvider;

use App\Domain\Campaign\Repository as CampaignRepository;

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
        $this->app->bind('Domain\Campaign\Repository', function($app) {
            return new CampaignRepository();
        });
    }
}
