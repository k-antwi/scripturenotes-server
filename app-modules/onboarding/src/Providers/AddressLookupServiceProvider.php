<?php

namespace Nucleus\Onboarding\Providers;

use Illuminate\Support\ServiceProvider;
use Nucleus\Onboarding\Services\AddressLookup\AddressLookupManager;
use Nucleus\Onboarding\Services\AddressLookup\Contracts\AddressLookupProvider;

class AddressLookupServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AddressLookupManager::class, function ($app) {
            return new AddressLookupManager($app);
        });

        // Bind the interface to the default driver returned by the manager
        $this->app->bind(AddressLookupProvider::class, function ($app) {
            return $app->make(AddressLookupManager::class)->driver();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
