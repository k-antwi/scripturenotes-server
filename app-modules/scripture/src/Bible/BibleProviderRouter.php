<?php

namespace Nucleus\Scripture\Bible;

use Nucleus\Scripture\Bible\Contracts\BibleProviderInterface;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;
use Nucleus\Scripture\Bible\Exceptions\QuotaExceededException;
use Nucleus\Scripture\Bible\Providers\ApiBibleProvider;
use Nucleus\Scripture\Bible\Providers\BibleBrainProvider;
use Nucleus\Scripture\Bible\Providers\BollsProvider;
use Nucleus\Scripture\Bible\Providers\FreeUseBibleProvider;
use Nucleus\Scripture\Bible\Providers\YouVersionProvider;

/**
 * BibleProviderRouter — selects the correct provider for each request.
 *
 * Routing rules:
 *  • Public-domain versions (KJV, ASV, WEB, YLT, DARBY, BBE, WNT)
 *      → FreeUseBibleProvider (no key, no quota, no limits)
 *      → NEVER routed to ApiBibleProvider
 *
 *  • Licensed text (NIV, ESV, NASB, NLT, …)
 *      → ApiBibleProvider → fallback: YouVersionProvider → fallback: FreeUseBibleProvider
 *
 *  • Audio / Video   → BibleBrainProvider (no fallback)
 *  • Semantic search → BollsProvider
 *  • Dictionary      → BollsProvider
 *  • Verse of day    → YouVersionProvider → fallback: FreeUseBibleProvider
 */
class BibleProviderRouter
{
    /** These versions are served exclusively by FreeUseBibleProvider */
    private readonly array $publicDomainVersions;

    /** Capability → single-provider class */
    private readonly array $capabilityMap;

    /** Capability → ordered fallback chain (class names) */
    private readonly array $fallbackChain;

    public function __construct(
        private readonly ApiBibleProvider $apiBible,
        private readonly FreeUseBibleProvider $freeUse,
        private readonly BibleBrainProvider $bibleBrain,
        private readonly BollsProvider $bolls,
        private readonly YouVersionProvider $youVersion,
    ) {
        $this->publicDomainVersions = ['KJV', 'ASV', 'WEB', 'YLT', 'DARBY', 'BBE', 'WNT'];

        $this->capabilityMap = [
            'audio'           => $this->bibleBrain,
            'video'           => $this->bibleBrain,
            'semantic_search' => $this->bolls,
            'dictionary'      => $this->bolls,
        ];

        $this->fallbackChain = [
            'text'        => [$this->apiBible, $this->youVersion, $this->freeUse],
            'verse_of_day' => [$this->youVersion, $this->freeUse],
        ];
    }

    /**
     * Resolve the primary provider for a passage/chapter request.
     * Public-domain versions are always routed to FreeUseBibleProvider.
     */
    public function forPassage(string $version): BibleProviderInterface
    {
        if (in_array(strtoupper($version), $this->publicDomainVersions, true)) {
            return $this->freeUse;
        }

        return $this->apiBible;
    }

    /**
     * Resolve the provider for a specific capability.
     * Single-provider capabilities (audio, video, dictionary, semantic_search) throw
     * directly when unavailable — there is no fallback defined.
     *
     * @throws \InvalidArgumentException  When the capability is unknown.
     */
    public function forCapability(string $capability): BibleProviderInterface
    {
        if (isset($this->capabilityMap[$capability])) {
            return $this->capabilityMap[$capability];
        }

        throw new \InvalidArgumentException("Unknown capability: [{$capability}]");
    }

    /**
     * Execute an operation with automatic fallback across the provider chain.
     *
     * Catches QuotaExceededException and ProviderUnavailableException from each
     * provider and moves to the next in the chain. Re-throws if all fail.
     *
     * @param  string    $capability  'text' or 'verse_of_day'
     * @param  callable  $operation   fn(BibleProviderInterface): array
     * @return array
     *
     * @throws ProviderUnavailableException  If every provider in the chain fails.
     */
    public function withFallback(string $capability, callable $operation): array
    {
        $chain = $this->fallbackChain[$capability] ?? [];

        if (empty($chain)) {
            throw new \InvalidArgumentException("No fallback chain defined for capability: [{$capability}]");
        }

        $lastException = null;

        foreach ($chain as $provider) {
            try {
                return $operation($provider);
            } catch (QuotaExceededException | ProviderUnavailableException $e) {
                $lastException = $e;

                continue;
            }
        }

        throw new ProviderUnavailableException(
            'all_providers',
            'All providers in the fallback chain failed. Last error: ' . $lastException?->getMessage(),
            502,
            $lastException,
        );
    }
}
