<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Generate https:// links in production, so a form POST (e.g. /logout)
        // is never sent to http:// and redirected (a redirect turns POST into GET).
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
