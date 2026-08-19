<?php

use Illuminate\Support\Facades\Http;
use Nucleus\Scripture\Models\Passage;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    // Prevent real HTTP calls during tests
    Http::preventStrayRequests();
});

it('returns a cached passage without hitting the API', function () {
    Passage::factory()->create([
        'book' => 'PRO',
        'chapter' => 19,
        'translation' => 'KJV',
        'content' => [
            'book' => 'PRO',
            'chapter' => 19,
            'translation' => 'KJV',
            'reference' => 'Proverbs 19',
            'totalChapters' => 31,
            'verses' => [
                ['number' => 1, 'text' => 'Better is the poor that walketh in his integrity.'],
            ],
        ],
        'fetched_at' => now(),
    ]);

    $response = $this->getJson('/api/passages/PRO/19?translation=KJV');

    $response->assertOk()
        ->assertJsonPath('book', 'PRO')
        ->assertJsonPath('chapter', 19)
        ->assertJsonPath('translation', 'KJV')
        ->assertJsonStructure(['verses']);
});

it('returns passage notes endpoint', function () {
    $response = $this->getJson('/api/passages/PRO/19/notes?translation=ESV');

    $response->assertOk()
        ->assertJsonStructure(['book', 'chapter', 'translation', 'notes']);
});

it('is case insensitive for book and translation parameters', function () {
    Passage::factory()->create([
        'book' => 'JHN',
        'chapter' => 3,
        'translation' => 'NIV',
        'content' => ['book' => 'JHN', 'chapter' => 3, 'translation' => 'NIV', 'verses' => []],
        'fetched_at' => now(),
    ]);

    $this->getJson('/api/passages/jhn/3?translation=niv')
        ->assertOk()
        ->assertJsonPath('book', 'JHN');
});
