<?php

namespace Nucleus\Kyc\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Nucleus\Kyc\Contracts\KycProviderInterface;
use Nucleus\Kyc\Events\KycFailed;
use Nucleus\Kyc\Events\KycMoreInfoRequested;
use Nucleus\Kyc\Events\KycSubmittedForReview;
use Nucleus\Kyc\Events\KycVerified;
use Nucleus\Kyc\Listeners\NotifyAdminOfNewSubmission;
use Nucleus\Kyc\Listeners\NotifyUserMoreInfoNeeded;
use Nucleus\Kyc\Listeners\NotifyUserOfFailure;
use Nucleus\Kyc\Listeners\NotifyUserOfVerification;
use Nucleus\Kyc\Livewire\KycDocumentUpload;
use Nucleus\Kyc\Livewire\KycLivenessCheck;
use Nucleus\Kyc\Livewire\KycStatus;
use Nucleus\Kyc\Middleware\KycReviewer;
use Nucleus\Kyc\Middleware\RequireKyc;
use Nucleus\Kyc\Services\KycProviderManager;

class KycServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KycProviderManager::class);

        // Allow resolving the active provider directly by its interface
        $this->app->bind(KycProviderInterface::class, function ($app) {
            return $app->make(KycProviderManager::class)->provider();
        });

        $this->mergeConfigFrom(__DIR__ . '/../../config/kyc.php', 'kyc');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../config/kyc.php' => config_path('kyc.php'),
        ], 'kyc-config');

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'kyc');

        $this->loadRoutesFrom(__DIR__ . '/../../routes/kyc-routes.php');

        Livewire::component('kyc.kyc-status', KycStatus::class);
        Livewire::component('kyc.kyc-document-upload', KycDocumentUpload::class);
        Livewire::component('kyc.kyc-liveness-check', KycLivenessCheck::class);

        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('kyc', RequireKyc::class);
        $router->aliasMiddleware('kyc.reviewer', KycReviewer::class);

        // ── Events & Listeners ────────────────────────────────────────────────
        Event::listen(KycSubmittedForReview::class, NotifyAdminOfNewSubmission::class);
        Event::listen(KycVerified::class, NotifyUserOfVerification::class);
        Event::listen(KycFailed::class, NotifyUserOfFailure::class);
        Event::listen(KycMoreInfoRequested::class, NotifyUserMoreInfoNeeded::class);
    }
}
