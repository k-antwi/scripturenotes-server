<?php

namespace Nucleus\Scripture\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Nucleus\Scripture\Bible\BibleCache;
use Nucleus\Scripture\Bible\BibleProviderRouter;
use Nucleus\Scripture\Bible\Providers\ApiBibleProvider;
use Nucleus\Scripture\Bible\Providers\BibleBrainProvider;
use Nucleus\Scripture\Bible\Providers\BollsProvider;
use Nucleus\Scripture\Bible\Providers\FreeUseBibleProvider;
use Nucleus\Scripture\Bible\Providers\YouVersionProvider;
use Nucleus\Scripture\Bible\ResponseNormalizer;
use Nucleus\Scripture\Services\ScriptureService;

class ScriptureServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/scripture.php', 'scripture');

        // ── Legacy service (backward-compatible with PassageController) ────────
        $this->app->singleton(ScriptureService::class);

        // ── Bible API gateway singletons ────────────────────────────────────────
        $this->app->singleton(ApiBibleProvider::class);
        $this->app->singleton(FreeUseBibleProvider::class);
        $this->app->singleton(BibleBrainProvider::class);
        $this->app->singleton(BollsProvider::class);
        $this->app->singleton(YouVersionProvider::class);
        $this->app->singleton(ResponseNormalizer::class);
        $this->app->singleton(BibleCache::class);

        $this->app->singleton(BibleProviderRouter::class, function ($app) {
            return new BibleProviderRouter(
                apiBible:   $app->make(ApiBibleProvider::class),
                freeUse:    $app->make(FreeUseBibleProvider::class),
                bibleBrain: $app->make(BibleBrainProvider::class),
                bolls:      $app->make(BollsProvider::class),
                youVersion: $app->make(YouVersionProvider::class),
            );
        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        /** @var Router $router */
        $router = $this->app->make(Router::class);

        // Legacy passage routes (unauthenticated cache proxy)
        $router->middleware(['api'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/scripture-api.php');
        });

        // New unified Bible API (Sanctum-authenticated)
        $router->middleware(['api'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/bible-api.php');
        });
    }
}
