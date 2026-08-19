<?php

namespace Nucleus\Kyc\Services;

use InvalidArgumentException;
use Nucleus\Kyc\Contracts\KycProviderInterface;
use Nucleus\Kyc\Services\Providers\JumioKycProvider;
use Nucleus\Kyc\Services\Providers\ManualKycProvider;
use Nucleus\Kyc\Services\Providers\OnfidoKycProvider;
use Nucleus\Kyc\Services\Providers\StripeKycProvider;
use Nucleus\Kyc\Services\Providers\VeriffKycProvider;

/**
 * KycProviderManager resolves the active KYC provider and maintains
 * a registry of all available providers.
 *
 * The active provider is selected via the `kyc.default_provider` config key.
 * Additional providers can be registered at runtime via `extend()`.
 */
class KycProviderManager
{
    /** @var array<string, class-string<KycProviderInterface>|KycProviderInterface> */
    protected array $providers = [];

    /** @var array<string, KycProviderInterface> */
    protected array $resolved = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    /**
     * Register all built-in providers.
     */
    protected function registerDefaults(): void
    {
        $this->providers = [
            'manual' => ManualKycProvider::class,
            'stripe' => StripeKycProvider::class,
            'onfido' => OnfidoKycProvider::class,
            'jumio'  => JumioKycProvider::class,
            'veriff' => VeriffKycProvider::class,
        ];
    }

    /**
     * Resolve and return the active provider as configured.
     */
    public function provider(?string $name = null): KycProviderInterface
    {
        $name ??= config('kyc.default_provider', 'manual');

        return $this->make($name);
    }

    /**
     * Resolve a provider instance by name, caching it for the request.
     */
    public function make(string $name): KycProviderInterface
    {
        if (isset($this->resolved[$name])) {
            return $this->resolved[$name];
        }

        if (! isset($this->providers[$name])) {
            throw new InvalidArgumentException("KYC provider [{$name}] is not registered.");
        }

        $provider = $this->providers[$name];

        $instance = $provider instanceof KycProviderInterface
            ? $provider
            : app($provider);

        return $this->resolved[$name] = $instance;
    }

    /**
     * Register a new provider or override an existing one.
     *
     * @param  string  $name
     * @param  class-string<KycProviderInterface>|KycProviderInterface  $provider
     */
    public function extend(string $name, $provider): static
    {
        $this->providers[$name] = $provider;
        unset($this->resolved[$name]); // clear cached instance

        return $this;
    }

    /**
     * Return all registered provider names.
     *
     * @return string[]
     */
    public function providerNames(): array
    {
        return array_keys($this->providers);
    }

    /**
     * Return an array of ['name' => string, 'label' => string] for all registered providers.
     *
     * @return array<int, array{name: string, label: string}>
     */
    public function providerList(): array
    {
        return array_map(function (string $name) {
            return [
                'name'  => $name,
                'label' => $this->make($name)->label(),
            ];
        }, $this->providerNames());
    }
}
