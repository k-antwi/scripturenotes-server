<?php

use Illuminate\Support\Facades\Cache;
use Nucleus\Scripture\Bible\BibleCache;

beforeEach(function () {
    Cache::flush();
    $this->bibleCache = app(BibleCache::class);
});

// ─── Passage cache ────────────────────────────────────────────────────────────

it('returns null on a cold passage cache', function () {
    $result = $this->bibleCache->getPassage('John 3:16', 'NIV');
    expect($result)->toBeNull();
});

it('returns data on a warm passage cache', function () {
    $data = ['data' => ['reference' => 'John 3:16', 'version' => 'NIV', 'verses' => []]];
    $this->bibleCache->putPassage('John 3:16', 'NIV', $data);

    $result = $this->bibleCache->getPassage('John 3:16', 'NIV');
    expect($result)->toBe($data);
});

// ─── Chapter cache ────────────────────────────────────────────────────────────

it('returns null on a cold chapter cache', function () {
    expect($this->bibleCache->getChapter('JHN', 3, 'KJV'))->toBeNull();
});

it('returns data on a warm chapter cache', function () {
    $data = ['data' => ['reference' => 'JHN 3', 'verses' => [['verse' => 16, 'text' => 'For God so loved…']]]];
    $this->bibleCache->putChapter('JHN', 3, 'KJV', $data);

    expect($this->bibleCache->getChapter('JHN', 3, 'KJV'))->toBe($data);
});

// ─── VOTD cache ───────────────────────────────────────────────────────────────

it('returns null on a cold votd cache', function () {
    expect($this->bibleCache->getVotd('2026-01-01'))->toBeNull();
});

it('caches verse of the day keyed by date', function () {
    $data = ['data' => ['reference' => 'Ps 23:1', 'version' => 'NIV']];
    $this->bibleCache->putVotd($data, '2026-01-01');

    expect($this->bibleCache->getVotd('2026-01-01'))->toBe($data);
    // Different date should return null
    expect($this->bibleCache->getVotd('2026-01-02'))->toBeNull();
});

// ─── Audio cache ──────────────────────────────────────────────────────────────

it('caches audio URLs and expires separately from text', function () {
    $data = ['data' => ['audio' => [['url' => 'https://cdn.example.com/kjv-gen1.mp3']]]];
    $this->bibleCache->putAudio('Genesis 1', 'KJV', $data);

    expect($this->bibleCache->getAudio('Genesis 1', 'KJV'))->toBe($data);
});

// ─── Search cache ─────────────────────────────────────────────────────────────

it('caches search results with type-aware key', function () {
    $keyword  = ['data' => ['verses' => []]];
    $semantic = ['data' => ['verses' => [['text' => 'semantic result']]]];

    $this->bibleCache->putSearch('faith', 'KJV', 'keyword', $keyword);
    $this->bibleCache->putSearch('faith', 'KJV', 'semantic', $semantic);

    expect($this->bibleCache->getSearch('faith', 'KJV', 'keyword'))->toBe($keyword);
    expect($this->bibleCache->getSearch('faith', 'KJV', 'semantic'))->toBe($semantic);
});

// ─── Dictionary cache ─────────────────────────────────────────────────────────

it('caches dictionary definitions', function () {
    $data = ['data' => ['word' => 'grace', 'definition' => ['unmerited favour']]];
    $this->bibleCache->putDictionary('grace', $data);

    expect($this->bibleCache->getDictionary('grace'))->toBe($data);
    expect($this->bibleCache->getDictionary('mercy'))->toBeNull();
});

// ─── Versions cache ───────────────────────────────────────────────────────────

it('caches version lists by language', function () {
    $en = ['data' => [['id' => 'KJV', 'name' => 'King James']]];
    $es = ['data' => [['id' => 'RVR', 'name' => 'Reina-Valera']]];

    $this->bibleCache->putVersions('en', $en);
    $this->bibleCache->putVersions('es', $es);

    expect($this->bibleCache->getVersions('en'))->toBe($en);
    expect($this->bibleCache->getVersions('es'))->toBe($es);
    expect($this->bibleCache->getVersions('fr'))->toBeNull();
});

// ─── Key format ───────────────────────────────────────────────────────────────

it('generates passage cache keys in the expected format', function () {
    $key = $this->bibleCache->passageKey('John 3:16', 'NIV');
    expect($key)->toStartWith('bible:passage:NIV:');
});

it('generates chapter cache keys in the expected format', function () {
    $key = $this->bibleCache->chapterKey('JHN', 3, 'KJV');
    expect($key)->toBe('bible:chapter:KJV:JHN:3');
});
