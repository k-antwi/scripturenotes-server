<?php

use Illuminate\Support\Facades\Cache;
use Nucleus\Scripture\Bible\BibleCache;
use Nucleus\Scripture\Bible\BibleProviderRouter;
use Nucleus\Scripture\Bible\Providers\ApiBibleProvider;
use Nucleus\Scripture\Bible\Providers\FreeUseBibleProvider;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
    // Prevent accidental real HTTP calls
    \Illuminate\Support\Facades\Http::preventStrayRequests();
});

// ─── /api/bible/passage ───────────────────────────────────────────────────────

it('returns 422 when ref is missing from passage endpoint', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/passage?version=KJV')
        ->assertUnprocessable();
});

it('returns cached passage with meta.cached=true on second request', function () {
    $cache = app(BibleCache::class);
    $data  = [
        'data' => [
            'reference' => 'John 3:16',
            'version'   => 'NIV',
            'verses'    => [['book' => 'JHN', 'chapter' => 3, 'verse' => 16, 'verseId' => 43003016, 'text' => 'For God so loved the world']],
        ],
        'meta' => ['provider' => 'api_bible', 'cached' => false],
    ];

    $cache->putPassage('John 3:16', 'NIV', $data);

    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/passage?ref=John+3:16&version=NIV')
        ->assertOk()
        ->assertJsonPath('meta.cached', true)
        ->assertJsonPath('data.version', 'NIV')
        ->assertJsonStructure(['data' => ['reference', 'version', 'verses'], 'meta']);
});

// ─── /api/bible/chapter ───────────────────────────────────────────────────────

it('returns 422 when book or chapter missing from chapter endpoint', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/chapter?book=JHN')
        ->assertUnprocessable();
});

it('returns unified shape from chapter endpoint', function () {
    $cache = app(BibleCache::class);
    $data  = [
        'data' => [
            'reference' => 'JHN 3',
            'version'   => 'KJV',
            'verses'    => [['book' => 'JHN', 'chapter' => 3, 'verse' => 16, 'verseId' => 43003016, 'text' => 'For God so loved the world']],
        ],
        'meta' => ['provider' => 'free_use', 'cached' => false],
    ];

    $cache->putChapter('JHN', 3, 'KJV', $data);

    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/chapter?book=JHN&chapter=3&version=KJV')
        ->assertOk()
        ->assertJsonStructure(['data' => ['reference', 'version', 'verses'], 'meta'])
        ->assertJsonPath('meta.cached', true);
});

// ─── /api/bible/search ────────────────────────────────────────────────────────

it('returns 422 when search query is missing', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/search?version=KJV')
        ->assertUnprocessable();
});

it('rejects invalid search type', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/search?q=faith&type=magic')
        ->assertUnprocessable();
});

// ─── /api/bible/dictionary ────────────────────────────────────────────────────

it('returns 422 when dictionary word is missing', function () {
    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/dictionary')
        ->assertUnprocessable();
});

// ─── /api/bible/verse-of-day ─────────────────────────────────────────────────

it('returns cached verse of day with meta.cached=true', function () {
    $cache = app(BibleCache::class);
    $data  = [
        'data' => ['reference' => 'Psalm 23:1', 'version' => 'NIV', 'verses' => []],
        'meta' => ['provider' => 'youversion', 'cached' => false],
    ];

    $cache->putVotd($data, now()->toDateString());

    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/verse-of-day')
        ->assertOk()
        ->assertJsonPath('meta.cached', true);
});

// ─── /api/bible/versions ─────────────────────────────────────────────────────

it('returns cached versions list', function () {
    $cache = app(BibleCache::class);
    $data  = [
        'data' => [['id' => 'KJV', 'name' => 'King James Version', 'abbreviation' => 'KJV', 'language' => 'eng']],
        'meta' => ['provider' => 'combined', 'cached' => false],
    ];

    $cache->putVersions('en', $data);

    $user = \App\Models\User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/bible/versions?language=en')
        ->assertOk()
        ->assertJsonPath('meta.cached', true)
        ->assertJsonStructure(['data', 'meta']);
});

// ─── Auth guard ───────────────────────────────────────────────────────────────

it('returns 401 for unauthenticated bible api requests', function () {
    $this->getJson('/api/bible/passage?ref=John+3:16&version=KJV')
        ->assertUnauthorized();
});
