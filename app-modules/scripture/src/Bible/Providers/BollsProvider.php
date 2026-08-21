<?php

namespace Nucleus\Scripture\Bible\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nucleus\Scripture\Bible\Contracts\BibleProviderInterface;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;

/**
 * Bolls Bible API provider — reserved EXCLUSIVELY for semantic search and dictionary lookups.
 *
 * This provider must never be used for standard text delivery.
 *
 * Docs: https://bolls.life/api/
 */
class BollsProvider implements BibleProviderInterface
{
    private const BASE_URL = 'https://bolls.life/api';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 10,
            'headers'  => ['Accept' => 'application/json'],
        ]);
    }

    public function name(): string
    {
        return 'bolls';
    }

    /**
     * @throws \LogicException Bolls is not a text provider — route to another provider for passages.
     */
    public function getPassage(string $reference, string $version): array
    {
        throw new \LogicException('BollsProvider does not serve passage text. Use ApiBibleProvider or FreeUseBibleProvider.');
    }

    /**
     * @throws \LogicException
     */
    public function getChapter(string $book, int $chapter, string $version): array
    {
        throw new \LogicException('BollsProvider does not serve chapter text. Use ApiBibleProvider or FreeUseBibleProvider.');
    }

    /**
     * Semantic / keyword search.
     *
     * Bolls endpoint: GET /bolls.life/api/search/{translation}/{word}/
     */
    public function search(string $query, string $version): array
    {
        $translation = strtoupper($version);
        $encodedQuery = rawurlencode($query);

        return $this->request("/search/{$translation}/{$encodedQuery}/");
    }

    /**
     * Dictionary lookup for a single word.
     *
     * Bolls endpoint: GET /bolls.life/api/definition/{word}/
     */
    public function getDictionary(string $word): array
    {
        $encoded = rawurlencode($word);

        return $this->request("/definition/{$encoded}/");
    }

    public function getVersions(?string $language = null): array
    {
        return $this->request('/translations/');
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['semantic_search', 'dictionary'], true);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /** @throws ProviderUnavailableException */
    private function request(string $path, array $options = []): array
    {
        try {
            $response = $this->client->get($path, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (GuzzleException $e) {
            throw new ProviderUnavailableException($this->name(), $e->getMessage(), 502, $e);
        }
    }
}
