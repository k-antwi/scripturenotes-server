<?php

namespace Nucleus\Annotations\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nucleus\Annotations\Observers\UserObserver;

class AnnotationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No bindings needed — controllers are resolved via DI automatically
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        // Auto-create the Untitled Notebook when a new user registers
        $userModel = config('auth.providers.users.model');
        $userModel::observe(UserObserver::class);

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->middleware(['api'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/annotations-api.php');
        });
    }
}
