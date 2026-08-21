<?php

namespace Nucleus\Scripture\Bible\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Nucleus\Scripture\Bible\Contracts\BibleProviderInterface;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;

/**
 * Free Use Bible API provider — no API key, no quota, no rate limits.
 *
 * Covers 1,000+ translations across 1,000+ languages.
 * This is the primary provider for all public-domain translations (KJV, ASV, WEB, YLT, DARBY).
 *
 * Base URL: https://bible-api.com
 * Docs:     https://free.bible / https://bible-api.com
 *
 * Response shape:
 *   { reference, text, verses[{book_id, book_name, chapter, verse, text}],
 *     translation_id, translation_name, translation_note }
 */
class FreeUseBibleProvider implements BibleProviderInterface
{
    private const BASE_URL = 'https://bible-api.com';

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
        return 'free_use';
    }

    public function getPassage(string $reference, string $version): array
    {
        // bible-api.com uses the translation as a query param
        return $this->request('/' . rawurlencode($reference), [
            'query' => ['translation' => strtolower($version)],
        ]);
    }

    public function getChapter(string $book, int $chapter, string $version): array
    {
        // bible-api.com accepts "Book Chapter" as the passage reference
        $reference = "{$book} {$chapter}";

        return $this->request('/' . rawurlencode($reference), [
            'query' => ['translation' => strtolower($version)],
        ]);
    }

    public function search(string $query, string $version): array
    {
        // Free Use Bible API does not support text search — return empty
        return ['results' => [], '_provider_note' => 'Text search not supported by free_use provider'];
    }

    public function getVersions(?string $language = null): array
    {
        // bible-api.com does not expose a versions endpoint
        // Return a static list of well-known public-domain translations
        $versions = [
            ['id' => 'kjv',   'name' => 'King James Version'],
            ['id' => 'asv',   'name' => 'American Standard Version'],
            ['id' => 'web',   'name' => 'World English Bible'],
            ['id' => 'ylt',   'name' => "Young's Literal Translation"],
            ['id' => 'darby', 'name' => 'Darby Translation'],
            ['id' => 'bbe',   'name' => 'Bible in Basic English'],
            ['id' => 'wnt',   'name' => "Weymouth New Testament"],
        ];

        return ['data' => $versions];
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['text'], true);
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
