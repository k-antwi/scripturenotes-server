<?php

namespace Nucleus\Providers\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nucleus\Providers\Contracts\CredentialVerifier;
use Nucleus\Providers\Http\Middleware\RedirectProviderRegistration;
use Nucleus\Providers\Models\ProviderProfile;

class ProvidersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/providers.php', 'providers');

        $this->app->bind(CredentialVerifier::class, function ($app) {
            return $app->make(config('providers.verifier'));
        });
    }

    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/provider-routes.php');

        $this->publishes([
            __DIR__ . '/../../config/providers.php' => config_path('providers.php'),
        ], 'nucleus-providers-config');

        $router->aliasMiddleware('provider.redirect', RedirectProviderRegistration::class);

        ProviderProfile::created(function (ProviderProfile $profile) {
            if (config('providers.auto_verify_on_create')) {
                \Nucleus\Providers\Jobs\VerifyProviderCredential::dispatch($profile);
            }
        });
    }
}
