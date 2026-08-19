<?php

namespace Nucleus\Permit\Providers;

use Illuminate\Support\ServiceProvider;

class PermitServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }
}
