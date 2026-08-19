<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Nucleus\Scripture\Services\ScriptureService;

/**
 * ScriptureService — unit tests for verse extraction and normalisation.
 *
 * The tests inject a mocked Guzzle client via reflection to avoid real HTTP calls
 * and bypass the Passage model (no DB needed for these unit tests).
 */

/**
 * Build a ScriptureService instance with a mocked HTTP client.
 * The mock queue is consumed in order; enqueue as many responses as needed.
 */
function makeServiceWithMock(array $responses): ScriptureService
{
    $mock = new MockHandler($responses);
    $handler = HandlerStack::create($mock);

    $service = new ScriptureService();

    // Inject the mock client via reflection (private $client property)
    $reflection = new ReflectionClass($service);
    $prop = $reflection->getProperty('client');
    $prop->setAccessible(true);
    $prop->setValue($service, new Client(['handler' => $handler]));

    return $service;
}

/**
 * Call a private method on ScriptureService for isolated unit testing.
 */
function callPrivate(ScriptureService $service, string $method, array $args = []): mixed
{
    $reflection = new ReflectionClass($service);
    $m = $reflection->getMethod($method);
    $m->setAccessible(true);
    return $m->invokeArgs($service, $args);
}

// ---------------------------------------------------------------------------
// normaliseChapter — structure tests (no HTTP call needed)
// ---------------------------------------------------------------------------

describe('normaliseChapter', function () {

    it('extracts verses nested inside paragraph blocks', function () {
        $service = new ScriptureService();

        $apiData = [
            'reference' => 'Genesis 1',
            'copyright' => 'Public Domain',
            'content' => [
                [
                    'type' => 'para',
                    'items' => [
                        [
                            'type' => 'verse',
                            'attrs' => ['number' => '1'],
                            'items' => [
                                ['type' => 'text', 'text' => 'In the beginning God created the heavens and the earth.'],
                            ],
                        ],
                        [
                            'type' => 'verse',
                            'attrs' => ['number' => '2'],
                            'items' => [
                                ['type' => 'text', 'text' => 'The earth was without form and void.'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = callPrivate($service, 'normaliseChapter', [$apiData, 'GEN', 1, 'KJV']);

        expect($result['verses'])->toHaveCount(2);
        expect($result['verses'][0]['number'])->toBe(1);
        expect($result['verses'][0]['text'])->toBe('In the beginning God created the heavens and the earth.');
        expect($result['verses'][1]['number'])->toBe(2);
    });

    it('handles verses spread across multiple paragraph blocks', function () {
        $service = new ScriptureService();

        $apiData = [
            'content' => [
                [
                    'type' => 'para',
                    'items' => [
                        buildVerseNode(1, 'Verse one text.'),
                    ],
                ],
                [
                    'type' => 'para',
                    'items' => [
                        buildVerseNode(2, 'Verse two text.'),
                        buildVerseNode(3, 'Verse three text.'),
                    ],
                ],
            ],
        ];

        $result = callPrivate($service, 'normaliseChapter', [$apiData, 'GEN', 1, 'KJV']);

        expect($result['verses'])->toHaveCount(3);
        expect($result['verses'][2]['number'])->toBe(3);
        expect($result['verses'][2]['text'])->toBe('Verse three text.');
    });

    it('concatenates inline char nodes inside a verse', function () {
        $service = new ScriptureService();

        $apiData = [
            'content' => [
                [
                    'type' => 'para',
                    'items' => [
                        [
                            'type' => 'verse',
                            'attrs' => ['number' => '1'],
                            'items' => [
                                ['type' => 'text', 'text' => 'For God '],
                                // "char" inline container (e.g. words of Christ in red-letter Bibles)
                                [
                                    'type' => 'char',
                                    'items' => [
                                        ['type' => 'text', 'text' => 'so loved'],
                                    ],
                                ],
                                ['type' => 'text', 'text' => ' the world.'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $result = callPrivate($service, 'normaliseChapter', [$apiData, 'JHN', 3, 'KJV']);

        expect($result['verses'][0]['text'])->toBe('For God so loved the world.');
    });

    it('deduplicates verse numbers that appear in multiple paragraph blocks', function () {
        $service = new ScriptureService();

        // Some translations split a single verse across two paragraphs
        $apiData = [
            'content' => [
                [
                    'type' => 'para',
                    'items' => [buildVerseNode(1, 'First part.')],
                ],
                [
                    'type' => 'para',
                    'items' => [buildVerseNode(1, 'Second part.')],
                ],
            ],
        ];

        $result = callPrivate($service, 'normaliseChapter', [$apiData, 'GEN', 1, 'KJV']);

        expect($result['verses'])->toHaveCount(1);
        expect($result['verses'][0]['text'])->toContain('First part.');
        expect($result['verses'][0]['text'])->toContain('Second part.');
    });

    it('ignores top-level blocks without items (e.g. section headers)', function () {
        $service = new ScriptureService();

        $apiData = [
            'content' => [
                // Section heading — no items
                ['type' => 's', 'text' => 'The Creation'],
                [
                    'type' => 'para',
                    'items' => [buildVerseNode(1, 'In the beginning.')],
                ],
            ],
        ];

        $result = callPrivate($service, 'normaliseChapter', [$apiData, 'GEN', 1, 'KJV']);

        expect($result['verses'])->toHaveCount(1);
    });

    it('returns empty verses array for an empty content block', function () {
        $service = new ScriptureService();

        $result = callPrivate($service, 'normaliseChapter', [[], 'GEN', 1, 'KJV']);

        expect($result['verses'])->toBeEmpty();
    });

    it('sets totalChapters from the known chapter-count map', function () {
        $service = new ScriptureService();

        $psalmResult = callPrivate($service, 'normaliseChapter', [[], 'PSA', 23, 'KJV']);
        $revelationResult = callPrivate($service, 'normaliseChapter', [[], 'REV', 1, 'KJV']);

        expect($psalmResult['totalChapters'])->toBe(150);
        expect($revelationResult['totalChapters'])->toBe(22);
    });

    it('sets totalChapters to 1 for unknown books', function () {
        $service = new ScriptureService();

        $result = callPrivate($service, 'normaliseChapter', [[], 'UNKNOWN', 1, 'KJV']);

        expect($result['totalChapters'])->toBe(1);
    });

    it('uses the API reference field when present', function () {
        $service = new ScriptureService();

        $result = callPrivate($service, 'normaliseChapter', [
            ['reference' => 'Genesis 1'],
            'GEN', 1, 'KJV',
        ]);

        expect($result['reference'])->toBe('Genesis 1');
    });

    it('falls back to book + chapter reference when API omits reference', function () {
        $service = new ScriptureService();

        $result = callPrivate($service, 'normaliseChapter', [[], 'GEN', 1, 'KJV']);

        expect($result['reference'])->toBe('GEN 1');
    });

    it('includes copyright from the API response', function () {
        $service = new ScriptureService();

        $result = callPrivate($service, 'normaliseChapter', [
            ['copyright' => '© 2001 Crossway'],
            'GEN', 1, 'ESV',
        ]);

        expect($result['copyright'])->toBe('© 2001 Crossway');
    });
});

// ---------------------------------------------------------------------------
// extractVerse — unit tests
// ---------------------------------------------------------------------------

describe('extractVerse', function () {

    it('returns null when verse number is zero or missing', function () {
        $service = new ScriptureService();

        $noNumber = ['type' => 'verse', 'attrs' => [], 'items' => []];
        $zeroNumber = ['type' => 'verse', 'attrs' => ['number' => '0'], 'items' => []];

        expect(callPrivate($service, 'extractVerse', [$noNumber]))->toBeNull();
        expect(callPrivate($service, 'extractVerse', [$zeroNumber]))->toBeNull();
    });

    it('reads verse number from attrs.number', function () {
        $service = new ScriptureService();

        $node = buildVerseNode(5, 'Some text.');
        $result = callPrivate($service, 'extractVerse', [$node]);

        expect($result['number'])->toBe(5);
        expect($result['text'])->toBe('Some text.');
    });

    it('trims leading and trailing whitespace from verse text', function () {
        $service = new ScriptureService();

        $node = [
            'type' => 'verse',
            'attrs' => ['number' => '1'],
            'items' => [
                ['type' => 'text', 'text' => '  Surrounded by spaces.  '],
            ],
        ];

        $result = callPrivate($service, 'extractVerse', [$node]);

        expect($result['text'])->toBe('Surrounded by spaces.');
    });
});

// ---------------------------------------------------------------------------
// extractText — unit tests
// ---------------------------------------------------------------------------

describe('extractText', function () {

    it('concatenates adjacent text items', function () {
        $service = new ScriptureService();

        $items = [
            ['type' => 'text', 'text' => 'Hello '],
            ['type' => 'text', 'text' => 'world.'],
        ];

        expect(callPrivate($service, 'extractText', [$items]))->toBe('Hello world.');
    });

    it('handles "tag" type items the same as text', function () {
        $service = new ScriptureService();

        $items = [
            ['type' => 'tag', 'text' => '¶ '],
            ['type' => 'text', 'text' => 'After pilcrow.'],
        ];

        expect(callPrivate($service, 'extractText', [$items]))->toBe('¶ After pilcrow.');
    });

    it('recurses into nested items for unknown container types', function () {
        $service = new ScriptureService();

        $items = [
            [
                'type' => 'char',
                'items' => [
                    ['type' => 'text', 'text' => 'Nested text.'],
                ],
            ],
        ];

        expect(callPrivate($service, 'extractText', [$items]))->toBe('Nested text.');
    });

    it('skips items with unrecognised types that have no sub-items', function () {
        $service = new ScriptureService();

        $items = [
            ['type' => 'note', 'caller' => 'a'],  // footnote marker — no text to extract
            ['type' => 'text', 'text' => 'Real text.'],
        ];

        expect(callPrivate($service, 'extractText', [$items]))->toBe('Real text.');
    });

    it('returns empty string for empty items array', function () {
        $service = new ScriptureService();

        expect(callPrivate($service, 'extractText', [[]]))->toBe('');
    });
});

// ---------------------------------------------------------------------------
// Helper
// ---------------------------------------------------------------------------

/**
 * Build a minimal API.Bible verse node.
 */
function buildVerseNode(int $number, string $text): array
{
    return [
        'type' => 'verse',
        'attrs' => ['number' => (string) $number],
        'items' => [
            ['type' => 'text', 'text' => $text],
        ],
    ];
}
