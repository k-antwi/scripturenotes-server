<?php

namespace Nucleus\Onboarding\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nucleus\Onboarding\Livewire\OnboardingWizard;
use Nucleus\Onboarding\Middleware\RequireOnboarding;

class OnboardingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(AddressLookupServiceProvider::class);
    }

    public function boot(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/onboarding.php', 'onboarding');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'onboarding');

        $this->publishes([
            __DIR__ . '/../../config/onboarding.php' => config_path('onboarding.php'),
        ], 'nucleus-onboarding-config');

        Livewire::component('onboarding.onboarding-wizard', OnboardingWizard::class);

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('onboarding', RequireOnboarding::class);
    }
}
