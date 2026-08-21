<?php

namespace Nucleus\Scripture\Bible\Providers;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Nucleus\Scripture\Bible\Contracts\BibleProviderInterface;
use Nucleus\Scripture\Bible\Exceptions\ProviderUnavailableException;
use Nucleus\Scripture\Bible\Exceptions\QuotaExceededException;

/**
 * Bible Brain (Faith Comes By Hearing) provider.
 *
 * Handles audio and video delivery exclusively.
 *
 * Docs: https://4.dbt.io/api_docs
 */
class BibleBrainProvider implements BibleProviderInterface
{
    private const BASE_URL = 'https://4.dbt.io/api';

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
        return 'bible_brain';
    }

    /**
     * @throws \LogicException BibleBrain is audio/video only.
     */
    public function getPassage(string $reference, string $version): array
    {
        throw new \LogicException('BibleBrainProvider does not serve passage text. Use ApiBibleProvider or FreeUseBibleProvider.');
    }

    /**
     * @throws \LogicException
     */
    public function getChapter(string $book, int $chapter, string $version): array
    {
        throw new \LogicException('BibleBrainProvider does not serve chapter text. Use ApiBibleProvider or FreeUseBibleProvider.');
    }

    /**
     * @throws \LogicException
     */
    public function search(string $query, string $version): array
    {
        throw new \LogicException('BibleBrainProvider does not support text search. Use ApiBibleProvider or BollsProvider.');
    }

    public function getVersions(?string $language = null): array
    {
        $params = [
            'key'  => config('scripture.bible_brain_key', ''),
            'v'    => 4,
        ];

        if ($language) {
            $params['language'] = $language;
        }

        return $this->request('/bibles', ['query' => $params]);
    }

    /**
     * Fetch streaming audio URLs for a passage reference.
     *
     * @param  string  $reference  e.g. "Psalm 23"
     * @param  string  $version    Translation code e.g. "KJV"
     * @return array               Array containing streaming URLs (signed, expire after 6 h)
     */
    public function getAudio(string $reference, string $version): array
    {
        $params = [
            'key'       => config('scripture.bible_brain_key', ''),
            'v'         => 4,
            'fileset_id' => $this->resolveAudioFileset($version),
        ];

        return $this->request('/bibles/filesets/download', ['query' => $params]);
    }

    /**
     * Fetch video content for a passage.
     */
    public function getVideo(string $reference, string $version): array
    {
        $params = [
            'key'        => config('scripture.bible_brain_key', ''),
            'v'          => 4,
            'fileset_id' => $this->resolveVideoFileset($version),
        ];

        return $this->request('/bibles/filesets/download', ['query' => $params]);
    }

    public function supports(string $capability): bool
    {
        return in_array($capability, ['audio', 'video'], true);
    }

    // ─── Private helpers ──────────────────────────────────────────────────────

    private function resolveAudioFileset(string $version): string
    {
        // Common FCBH audio fileset IDs (drama, MP3)
        $filesets = [
            'KJV'  => 'ENGESVO2DA',
            'NIV'  => 'ENGNIVO2DA',
            'ESV'  => 'ENGESVO2DA',
            'NKJV' => 'ENGNKJO2DA',
        ];

        return $filesets[strtoupper($version)] ?? $filesets['KJV'];
    }

    private function resolveVideoFileset(string $version): string
    {
        return 'ENGWBTVP2ET'; // Jesus Film — widely available
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
