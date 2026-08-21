<?php

namespace Nucleus\Scripture\Bible;

use Illuminate\Support\Facades\Cache;

/**
 * BibleCache — Redis-backed cache layer for the Bible API gateway.
 *
 * TTLs (in seconds):
 *   Text (passage/chapter): 30 days  — scripture text is immutable
 *   Version list:           30 days
 *   Verse of the day:       24 hours  (keyed by date)
 *   Audio streaming URLs:   6 hours   (signed URLs expire)
 *   Search results:         1 hour
 *   Dictionary:             30 days   (definitions don't change)
 *
 * Cache key format: bible:{type}:{version}:{hash}
 *   e.g. bible:passage:NIV:a1b2c3d4
 *        bible:votd:2026-08-20
 */
class BibleCache
{
    private const TTL_TEXT       = 2_592_000; // 30 days
    private const TTL_VERSIONS   = 2_592_000; // 30 days
    private const TTL_VOTD       = 86_400;    // 24 hours
    private const TTL_AUDIO      = 21_600;    // 6 hours
    private const TTL_SEARCH     = 3_600;     // 1 hour
    private const TTL_DICTIONARY = 2_592_000; // 30 days

    // ─── Passage / Chapter ────────────────────────────────────────────────────

    public function getPassage(string $reference, string $version): ?array
    {
        return Cache::get($this->passageKey($reference, $version));
    }

    public function putPassage(string $reference, string $version, array $data): void
    {
        Cache::put($this->passageKey($reference, $version), $data, self::TTL_TEXT);
    }

    public function getChapter(string $book, int $chapter, string $version): ?array
    {
        return Cache::get($this->chapterKey($book, $chapter, $version));
    }

    public function putChapter(string $book, int $chapter, string $version, array $data): void
    {
        Cache::put($this->chapterKey($book, $chapter, $version), $data, self::TTL_TEXT);
    }

    // ─── Verse of the Day ────────────────────────────────────────────────────

    public function getVotd(?string $date = null): ?array
    {
        return Cache::get($this->votdKey($date));
    }

    public function putVotd(array $data, ?string $date = null): void
    {
        Cache::put($this->votdKey($date), $data, self::TTL_VOTD);
    }

    // ─── Audio ───────────────────────────────────────────────────────────────

    public function getAudio(string $reference, string $version): ?array
    {
        return Cache::get($this->audioKey($reference, $version));
    }

    public function putAudio(string $reference, string $version, array $data): void
    {
        Cache::put($this->audioKey($reference, $version), $data, self::TTL_AUDIO);
    }

    // ─── Search ──────────────────────────────────────────────────────────────

    public function getSearch(string $query, string $version, string $type): ?array
    {
        return Cache::get($this->searchKey($query, $version, $type));
    }

    public function putSearch(string $query, string $version, string $type, array $data): void
    {
        Cache::put($this->searchKey($query, $version, $type), $data, self::TTL_SEARCH);
    }

    // ─── Dictionary ──────────────────────────────────────────────────────────

    public function getDictionary(string $word): ?array
    {
        return Cache::get($this->dictionaryKey($word));
    }

    public function putDictionary(string $word, array $data): void
    {
        Cache::put($this->dictionaryKey($word), $data, self::TTL_DICTIONARY);
    }

    // ─── Versions ────────────────────────────────────────────────────────────

    public function getVersions(?string $language): ?array
    {
        return Cache::get($this->versionsKey($language));
    }

    public function putVersions(?string $language, array $data): void
    {
        Cache::put($this->versionsKey($language), $data, self::TTL_VERSIONS);
    }

    // ─── Generic helpers ─────────────────────────────────────────────────────

    /**
     * Retrieve a cached value or compute and store it.
     *
     * @param  string    $key
     * @param  int       $ttl
     * @param  callable  $callback
     * @return array
     */
    public function remember(string $key, int $ttl, callable $callback): array
    {
        return Cache::remember($key, $ttl, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    // ─── Key builders ────────────────────────────────────────────────────────

    public function passageKey(string $reference, string $version): string
    {
        return 'bible:passage:' . strtoupper($version) . ':' . $this->hash($reference);
    }

    public function chapterKey(string $book, int $chapter, string $version): string
    {
        return 'bible:chapter:' . strtoupper($version) . ':' . strtoupper($book) . ':' . $chapter;
    }

    public function votdKey(?string $date): string
    {
        return 'bible:votd:' . ($date ?? now()->toDateString());
    }

    public function audioKey(string $reference, string $version): string
    {
        return 'bible:audio:' . strtoupper($version) . ':' . $this->hash($reference);
    }

    public function searchKey(string $query, string $version, string $type): string
    {
        return 'bible:search:' . strtoupper($version) . ':' . $type . ':' . $this->hash($query);
    }

    public function dictionaryKey(string $word): string
    {
        return 'bible:dictionary:' . strtolower($word);
    }

    public function versionsKey(?string $language): string
    {
        return 'bible:versions:' . ($language ?? 'all');
    }

    private function hash(string $value): string
    {
        return substr(md5($value), 0, 8);
    }
}
