<?php

namespace Nucleus\Scripture\Bible\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Nucleus\Scripture\Bible\Contracts\BibleProviderInterface;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;
use Nucleus\Scripture\Bible\Exceptions\QuotaExceededException;

/**
 * YouVersion Platform provider.
 *
 * Primary use: Verse of the Day.
 * Secondary use: Fallback text provider in the licensed-translation chain.
 *
 * Docs: https://developers.youversion.com/
 */
class YouVersionProvider implements BibleProviderInterface
{
    private const BASE_URL = 'https://developers.youversion.com/1.0';

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 10,
            'headers'  => [
                'X-YouVersion-App-Token' => config('scripture.youversion_app_key', ''),
                'Accept'                 => 'application/json',
            ],
        ]);
    }

    public function name(): string
    {
        return 'youversion';
    }

    public function getPassage(string $reference, string $version): array
    {
        return $this->request('/verse.json', [
            'query' => [
                'passage' => $reference,
                'version' => $this->resolveVersionId($version),
            ],
        ]);
    }

    public function getChapter(string $book, int $chapter, string $version): array
    {
        return $this->request('/verse.json', [
            'query' => [
                'passage' => "{$book} {$chapter}",
                'version' => $this->resolveVersionId($version),
            ],
        ]);
    }

    public function search(string $query, string $version): array
    {
        // YouVersion search is not part of the public Platform API
        return ['results' => [], '_provider_note' => 'Search not supported by YouVersion provider'];
    }

    /**
     * Verse of the Day — YouVersion's primary public capability.
     */
    public function getVerseOfDay(?string $date = null): array
    {
        $params = [];
        if ($date !== null) {
            $params['day'] = $date; // YYYY-MM-DD
        }

        return $this->request('/verse_of_the_day.json', ['query' => $params]);
    }

    public function getVersions(?string $language = null): array
    {
        $params = $language ? ['language_tag' => $language] : [];

        return $this->request('/versions.json', ['query' => $params]);
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['text', 'verse_of_day'], true);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    /**
     * YouVersion uses numeric version IDs rather than translation codes.
     * Fallback to NIV (111) which is YouVersion's default.
     */
    private function resolveVersionId(string $version): int
    {
        $versionMap = [
            'KJV'  => 1,
            'ASV'  => 8,
            'NIV'  => 111,
            'ESV'  => 59,
            'NKJV' => 114,
            'NLT'  => 116,
            'NASB' => 100,
        ];

        return $versionMap[strtoupper($version)] ?? 111;
    }

    /** @throws QuotaExceededException|ProviderUnavailableException */
    private function request(string $path, array $options = []): array
    {
        try {
            $response = $this->client->get($path, $options);

            return json_decode($response->getBody()->getContents(), true) ?? [];
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 429) {
                throw new QuotaExceededException($this->name(), 429, $e);
            }
            throw new ProviderUnavailableException($this->name(), $e->getMessage(), $e->getCode(), $e);
        } catch (GuzzleException $e) {
            throw new ProviderUnavailableException($this->name(), $e->getMessage(), 502, $e);
        }
    }
}
