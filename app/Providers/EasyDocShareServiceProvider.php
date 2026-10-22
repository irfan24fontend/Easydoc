<?php

namespace App\Providers;

use App\Http\Middleware\EasyDocInject;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Ek line e shob feature chalu kore: preloader, public prescription link,
 * JPG/PDF download, mobile doctor sidebar. Layout/blade edit lagbe na.
 */
class EasyDocShareServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('views/easydoc'), 'easydoc');

        $this->app['router']->pushMiddlewareToGroup('web', EasyDocInject::class);

        Route::middleware('web')->group(base_path('routes/easydoc.php'));
    }
}
