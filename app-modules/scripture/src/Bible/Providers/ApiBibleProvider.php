<?php

namespace Nucleus\Scripture\Bible\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Nucleus\Scripture\Bible\Contracts\BibleProviderInterface;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;
use Nucleus\Scripture\Bible\Exceptions\QuotaExceededException;

/**
 * API.Bible (American Bible Society) provider.
 *
 * Handles licensed translations: NIV, ESV, NASB, NLT, NKJV, BSB etc.
 * Public-domain translations must never be routed here — use FreeUseBibleProvider.
 *
 * Docs: https://scripture.api.bible/
 */
class ApiBibleProvider implements BibleProviderInterface
{
    private const BASE_URL = 'https://api.scripture.api.bible/v1';

    /** Translation code → API.Bible bible-id */
    private const BIBLE_IDS = [
        'NIV'  => '78a9f6124f344018-01',
        'ESV'  => '592420522e16049f-01',
        'NASB' => '40072c4a5aba4022-01',
        'NLT'  => '65eec8e0b60e656b-01',
        'NKJV' => 'c315fa9f71d4af3a-01',
        'BSB'  => '7142879509583d59-04',
        // KJV kept as absolute fallback only; router should never send KJV here
        'KJV'  => 'de4e12af7f28f599-02',
        'ASV'  => '06125adad2d5898a-01',
    ];

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 10,
            'headers'  => [
                'api-key' => config('scripture.api_bible_key', ''),
                'Accept'  => 'application/json',
            ],
        ]);
    }

    public function name(): string
    {
        return 'api_bible';
    }

    public function getPassage(string $reference, string $version): array
    {
        $bibleId = $this->resolveBibleId($version);

        return $this->request("GET", "/bibles/{$bibleId}/passages", [
            'query' => [
                'passage-id'           => $this->encodeReference($reference),
                'content-type'         => 'json',
                'include-verse-numbers' => 'true',
                'include-verse-spans'  => 'true',
            ],
        ]);
    }

    public function getChapter(string $book, int $chapter, string $version): array
    {
        $bibleId  = $this->resolveBibleId($version);
        $chapterId = strtoupper($book) . '.' . $chapter;

        return $this->request("GET", "/bibles/{$bibleId}/chapters/{$chapterId}", [
            'query' => [
                'content-type'          => 'json',
                'include-notes'         => 'true',
                'include-verse-numbers' => 'true',
                'include-verse-spans'   => 'true',
            ],
        ]);
    }

    public function search(string $query, string $version): array
    {
        $bibleId = $this->resolveBibleId($version);

        return $this->request("GET", "/bibles/{$bibleId}/search", [
            'query' => ['query' => $query, 'limit' => 20],
        ]);
    }

    public function getVersions(?string $language = null): array
    {
        $params = $language ? ['language' => $language] : [];

        return $this->request("GET", "/bibles", ['query' => $params]);
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['text', 'verse_of_day'], true);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function resolveBibleId(string $version): string
    {
        return self::BIBLE_IDS[strtoupper($version)] ?? self::BIBLE_IDS['KJV'];
    }

    private function encodeReference(string $reference): string
    {
        // API.Bible expects OSIS passage IDs — do a simple conversion
        return urlencode($reference);
    }

    /**
     * @throws QuotaExceededException
     * @throws ProviderUnavailableException
     */
    private function request(string $method, string $path, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $path, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (ClientException $e) {
            $statusCode = $e->getResponse()->getStatusCode();

            if ($statusCode === 429) {
                throw new QuotaExceededException($this->name(), 429, $e);
            }

            throw new ProviderUnavailableException($this->name(), $e->getMessage(), $statusCode, $e);
        } catch (GuzzleException $e) {
            throw new ProviderUnavailableException($this->name(), $e->getMessage(), 502, $e);
        }
    }
}
