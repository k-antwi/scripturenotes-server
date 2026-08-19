<?php

namespace Nucleus\Annotations\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class AnnotationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings needed — controllers are resolved via DI automatically
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->middleware(['api'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/annotations-api.php');
        });
    }
}
