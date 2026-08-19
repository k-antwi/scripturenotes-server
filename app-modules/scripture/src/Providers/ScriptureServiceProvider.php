<?php

namespace Nucleus\Scripture\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nucleus\Scripture\Services\ScriptureService;

class ScriptureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/scripture.php', 'scripture');

        $this->app->singleton(ScriptureService::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->middleware(['api'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/scripture-api.php');
        });
    }
}
