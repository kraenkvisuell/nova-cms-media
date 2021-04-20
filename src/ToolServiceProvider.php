<?php

namespace Kraenkvisuell\NovaCmsMedia;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Kraenkvisuell\NovaCmsMedia\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-cms-media');
        $this->loadJsonTranslationsFrom(resource_path('lang/vendor/nova-cms-media'));
        
        $this->publishes([
            __DIR__.'/../config/' => config_path(),
            __DIR__.'/../database/' => base_path('/database/migrations'),
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/nova-cms-media'),
        ], 'config-nova-cms-media');

        $this->app->booted(function () {
            $this->routes();
        });
    }

    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/nova-cms-media')
                ->group(__DIR__.'/../routes/api.php');
    }
}
