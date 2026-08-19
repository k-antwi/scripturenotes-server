<?php

namespace Nucleus\Brain\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Laravel\Ai\Contracts\ConversationStore;
use Livewire\Livewire;
use Nucleus\Brain\Contracts\BrainConversationStore;
use Nucleus\Brain\Livewire\BrainChat;
use Nucleus\Brain\Models\AiModel;
use Nucleus\Brain\Services\BrainService;
use Nucleus\Brain\Storage\CouchbaseConversationStore;

class BrainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/brain.php', 'brain');

        $this->app->singleton(BrainService::class);

        // Bind both the Laravel AI SDK contract and the Brain-specific extension
        // to our Couchbase-backed store (which falls back to SQL automatically).
        $this->app->singleton(CouchbaseConversationStore::class);
        $this->app->bind(ConversationStore::class, CouchbaseConversationStore::class);
        $this->app->bind(BrainConversationStore::class, CouchbaseConversationStore::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'brain');

        Livewire::component('brain.brain-chat', BrainChat::class);

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->middleware(['web'])->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/brain-routes.php');
        });

        // Bridge the active AiModel's API key into the Laravel AI provider config
        // so agents can call ->prompt(provider: ...) without needing .env keys.
        // The model is cached for 5 minutes; AiModel::activate() clears the cache
        // on change so the new active model takes effect on the next request.
        $this->app->booted(function () {
            try {
                $activeModel = cache()->remember(
                    'brain.active_model',
                    300,
                    fn () => AiModel::getActive()
                );

                if ($activeModel?->api_key) {
                    $provider = $activeModel->provider;

                    config([
                        "ai.providers.{$provider}" => array_merge(
                            config("ai.providers.{$provider}", []),
                            ['driver' => $provider, 'key' => $activeModel->api_key]
                        ),
                        'ai.default' => $provider,
                    ]);
                }
            } catch (\Throwable) {
                // DB may not be ready during artisan package:discover or CI installs.
            }
        });
    }
}
