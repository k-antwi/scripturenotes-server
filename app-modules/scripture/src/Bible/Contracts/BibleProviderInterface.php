<?php

namespace Nucleus\Scripture\Bible\Contracts;

interface BibleProviderInterface
{
    /**
     * A unique slug identifying this provider (e.g. 'api_bible', 'free_use').
     */
    public function name(): string;

    /**
     * Fetch a single passage / verse range.
     *
     * @param  string  $reference  Human-readable reference e.g. "John 3:16" or "Romans 8:1-4"
     * @param  string  $version    Translation code e.g. "NIV", "KJV"
     * @return array               Raw provider response (normalised by ResponseNormalizer)
     */
    public function getPassage(string $reference, string $version): array;

    /**
     * Fetch a full chapter.
     *
     * @param  string  $book     Book abbreviation e.g. "JHN", "ROM"
     * @param  int     $chapter  Chapter number
     * @param  string  $version  Translation code
     * @return array
     */
    public function getChapter(string $book, int $chapter, string $version): array;

    /**
     * Full-text or semantic keyword search.
     *
     * @param  string  $query    Search terms
     * @param  string  $version  Translation to search within
     * @return array
     */
    public function search(string $query, string $version): array;

    /**
     * List available translations, optionally filtered by language.
     *
     * @param  string|null  $language  ISO 639-1 code e.g. "en"
     * @return array
     */
    public function getVersions(?string $language = null): array;

    /**
     * Whether this provider supports the given capability.
     *
     * Capability slugs: 'text', 'audio', 'video', 'semantic_search',
     *                   'dictionary', 'verse_of_day'
     */
    public function supports(string $capability): bool;
}
