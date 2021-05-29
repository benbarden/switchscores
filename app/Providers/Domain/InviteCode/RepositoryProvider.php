<?php

namespace App\Providers\Domain\InviteCode;

use Illuminate\Support\ServiceProvider;

use App\Domain\InviteCode\Repository as InviteCodeRepository;

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
        $this->app->bind('Domain\InviteCode\Repository', function($app) {
            return new InviteCodeRepository();
        });
    }
}
