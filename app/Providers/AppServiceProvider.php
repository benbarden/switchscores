<?php

namespace App\Providers;

use App\Domain\Cache\CacheManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if (App::environment('local', 'prod')) {
            $this->app->register(DbSlowQueryProvider::class);
        }

        $this->app->bind(CacheManager::class, function($app) {
            return new CacheManager();
        });
    }
}
