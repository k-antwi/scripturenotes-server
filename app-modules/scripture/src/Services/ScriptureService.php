<?php

namespace Nucleus\Scripture\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Nucleus\Scripture\Models\Passage;

/**
 * Scripture Service — cache-first Bible API proxy (PRD §4.1).
 *
 * Public-domain translations (KJV, ASV) are fetched from the Free Use
 * Bible API (api.scripture.api.bible / ao-lab).  Copyrighted translations
 * (NIV, NKJV, ESV, NLT) go through API.Bible (ABS) which handles FUMS
 * usage tracking (PRD §3 Technology Stack).
 */
class ScriptureService
{
    private Client $client;

    // Free Use Bible API (public domain translations)
    private const FREE_API_BASE = 'https://api.scripture.api.bible/v1';

    // Translation → API.Bible bible-id mapping
    private const TRANSLATION_IDS = [
        'KJV'  => 'de4e12af7f28f599-02',
        'ASV'  => '06125adad2d5898a-01',
        'ESV'  => '592420522e16049f-01',
        'NIV'  => '78a9f6124f344018-01',
        'NKJV' => 'c315fa9f71d4af3a-01',
        'BSB'  => '7142879509583d59-04',
        'NLT'  => '65eec8e0b60e656b-01',
    ];

    // Total chapter counts per book (standard Protestant canon)
    private const BOOK_CHAPTER_COUNTS = [
        'GEN' => 50,  'EXO' => 40,  'LEV' => 27,  'NUM' => 36,  'DEU' => 34,
        'JOS' => 24,  'JDG' => 21,  'RUT' => 4,   '1SA' => 31,  '2SA' => 24,
        '1KI' => 22,  '2KI' => 25,  '1CH' => 29,  '2CH' => 36,  'EZR' => 10,
        'NEH' => 13,  'EST' => 10,  'JOB' => 42,  'PSA' => 150, 'PRO' => 31,
        'ECC' => 12,  'SNG' => 8,   'ISA' => 66,  'JER' => 52,  'LAM' => 5,
        'EZK' => 48,  'DAN' => 12,  'HOS' => 14,  'JOL' => 3,   'AMO' => 9,
        'OBA' => 1,   'JON' => 4,   'MIC' => 7,   'NAM' => 3,   'HAB' => 3,
        'ZEP' => 3,   'HAG' => 2,   'ZEC' => 14,  'MAL' => 4,
        'MAT' => 28,  'MRK' => 16,  'LUK' => 24,  'JHN' => 21,  'ACT' => 28,
        'ROM' => 16,  '1CO' => 16,  '2CO' => 13,  'GAL' => 6,   'EPH' => 6,
        'PHP' => 4,   'COL' => 4,   '1TH' => 5,   '2TH' => 3,   '1TI' => 6,
        '2TI' => 4,   'TIT' => 3,   'PHM' => 1,   'HEB' => 13,  'JAS' => 5,
        '1PE' => 5,   '2PE' => 3,   '1JN' => 5,   '2JN' => 1,   '3JN' => 1,
        'JUD' => 1,   'REV' => 22,
    ];

    // Bible book abbreviation → OSIS/API.Bible book ID
    private const BOOK_IDS = [
        'GEN' => 'GEN', 'EXO' => 'EXO', 'LEV' => 'LEV', 'NUM' => 'NUM',
        'DEU' => 'DEU', 'JOS' => 'JOS', 'JDG' => 'JDG', 'RUT' => 'RUT',
        '1SA' => '1SA', '2SA' => '2SA', '1KI' => '1KI', '2KI' => '2KI',
        '1CH' => '1CH', '2CH' => '2CH', 'EZR' => 'EZR', 'NEH' => 'NEH',
        'EST' => 'EST', 'JOB' => 'JOB', 'PSA' => 'PSA', 'PRO' => 'PRO',
        'ECC' => 'ECC', 'SNG' => 'SNG', 'ISA' => 'ISA', 'JER' => 'JER',
        'LAM' => 'LAM', 'EZK' => 'EZK', 'DAN' => 'DAN', 'HOS' => 'HOS',
        'JOL' => 'JOL', 'AMO' => 'AMO', 'OBA' => 'OBA', 'JON' => 'JON',
        'MIC' => 'MIC', 'NAM' => 'NAM', 'HAB' => 'HAB', 'ZEP' => 'ZEP',
        'HAG' => 'HAG', 'ZEC' => 'ZEC', 'MAL' => 'MAL',
        'MAT' => 'MAT', 'MRK' => 'MRK', 'LUK' => 'LUK', 'JHN' => 'JHN',
        'ACT' => 'ACT', 'ROM' => 'ROM', '1CO' => '1CO', '2CO' => '2CO',
        'GAL' => 'GAL', 'EPH' => 'EPH', 'PHP' => 'PHP', 'COL' => 'COL',
        '1TH' => '1TH', '2TH' => '2TH', '1TI' => '1TI', '2TI' => '2TI',
        'TIT' => 'TIT', 'PHM' => 'PHM', 'HEB' => 'HEB', 'JAS' => 'JAS',
        '1PE' => '1PE', '2PE' => '2PE', '1JN' => '1JN', '2JN' => '2JN',
        '3JN' => '3JN', 'JUD' => 'JUD', 'REV' => 'REV',
    ];

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'headers' => [
                'api-key' => config('scripture.api_bible_key', ''),
            ],
        ]);
    }

    /**
     * Get a passage — cache hit returns immediately; cache miss fetches from API.
     * Returns normalised passage array compatible with the mobile client schema.
     *
     * @return array{book: string, chapter: int, translation: string, reference: string, verses: array, totalChapters: int}
     */
    public function getPassage(string $book, int $chapter, string $translation = 'KJV'): array
    {
        $book = strtoupper($book);
        $translation = strtoupper($translation);

        $cached = Passage::findCached($book, $chapter, $translation);
        if ($cached) {
            return $cached->content;
        }

        $content = $this->fetchFromApi($book, $chapter, $translation);

        Passage::upsertFromApi($book, $chapter, $translation, $content);

        return $content;
    }

    /**
     * Get study notes for a chapter from the ESV Global Study Bible dataset
     * or user-added notes (PRD §5.3).
     *
     * Returns an array of note objects keyed by verse number.
     */
    public function getStudyNotes(string $book, int $chapter, string $translation = 'ESV'): array
    {
        // In future this would query the ESV Global SB notes endpoint via
        // API.Bible datasets.  For now return an empty structure so the
        // mobile client's cache-first pattern works correctly.
        return [
            'book' => strtoupper($book),
            'chapter' => $chapter,
            'translation' => strtoupper($translation),
            'notes' => [],
        ];
    }

    /**
     * Fetch from API.Bible and normalise into our schema.
     */
    private function fetchFromApi(string $book, int $chapter, string $translation): array
    {
        $bibleId = self::TRANSLATION_IDS[$translation] ?? self::TRANSLATION_IDS['KJV'];
        $bookId = self::BOOK_IDS[$book] ?? $book;
        $chapterId = "{$bookId}.{$chapter}";

        try {
            $response = $this->client->get(
                self::FREE_API_BASE . "/bibles/{$bibleId}/chapters/{$chapterId}",
                [
                    'query' => [
                        'content-type' => 'json',
                        'include-notes' => 'true',
                        'include-verse-numbers' => 'true',
                        'include-verse-spans' => 'true',
                    ],
                ]
            );

            $raw = json_decode($response->getBody()->getContents(), true);

            return $this->normaliseChapter($raw['data'] ?? [], $book, $chapter, $translation);
        } catch (GuzzleException $e) {
            Log::warning('ScriptureService: API fetch failed', [
                'book' => $book,
                'chapter' => $chapter,
                'translation' => $translation,
                'error' => $e->getMessage(),
            ]);

            // Re-throw so the controller returns a 502 and the mobile client's
            // error handler shows "Could not load this passage" instead of
            // silently rendering a blank passage with 0 verses.
            throw $e;
        }
    }

    /**
     * Normalise the API.Bible chapter response into our client-expected shape:
     *
     * {
     *   book, chapter, translation, reference, totalChapters,
     *   verses: [{ number, text }]
     * }
     *
     * API.Bible wraps verses inside paragraph blocks:
     *   content → [{ type:"para", items:[{ type:"verse", attrs:{number:"1"}, items:[{type:"text", text:"..."}] }] }]
     *
     * We recurse one level into any block-level container to find verse nodes.
     */
    private function normaliseChapter(array $data, string $book, int $chapter, string $translation): array
    {
        $verses = [];

        foreach ($data['content'] ?? [] as $block) {
            $blockType = $block['type'] ?? '';

            if ($blockType === 'verse') {
                // Top-level verse (some API responses may use flat structure)
                $verse = $this->extractVerse($block);
                if ($verse !== null) {
                    $verses[] = $verse;
                }
            } elseif (isset($block['items']) && is_array($block['items'])) {
                // Block-level container (e.g. "para", "s", "ms", "q")
                foreach ($block['items'] as $item) {
                    if (($item['type'] ?? '') === 'verse') {
                        $verse = $this->extractVerse($item);
                        if ($verse !== null) {
                            $verses[] = $verse;
                        }
                    }
                }
            }
        }

        // Deduplicate by verse number (some translations repeat cross-paragraph verses)
        $uniqueVerses = [];
        $seen = [];
        foreach ($verses as $verse) {
            $num = $verse['number'];
            if (isset($seen[$num])) {
                // Append additional text segments for the same verse number
                $uniqueVerses[$seen[$num]]['text'] .= ' ' . $verse['text'];
            } else {
                $seen[$num] = count($uniqueVerses);
                $uniqueVerses[] = $verse;
            }
        }

        $totalChapters = self::BOOK_CHAPTER_COUNTS[$book] ?? 1;

        return [
            'book' => $book,
            'chapter' => $chapter,
            'translation' => $translation,
            'reference' => $data['reference'] ?? "{$book} {$chapter}",
            'totalChapters' => $totalChapters,
            'verses' => array_values($uniqueVerses),
            'copyright' => $data['copyright'] ?? null,
        ];
    }

    /**
     * Extract a single verse node into our { number, text } shape.
     * API.Bible verse node structure:
     *   { type:"verse", attrs:{number:"1"}, items:[{ type:"text", text:"In the beginning..." }, ...] }
     *
     * Returns null when the verse number cannot be determined.
     */
    private function extractVerse(array $verseNode): ?array
    {
        // Verse number lives in attrs.number (string) on the verse node itself
        $verseNum = (int) ($verseNode['attrs']['number'] ?? $verseNode['number'] ?? 0);

        if ($verseNum === 0) {
            return null;
        }

        $text = $this->extractText($verseNode['items'] ?? []);

        return [
            'number' => $verseNum,
            'text' => trim($text),
        ];
    }

    /**
     * Recursively concatenate text from an items array.
     * Handles nested structures (e.g. char nodes wrapping text nodes).
     */
    private function extractText(array $items): string
    {
        $text = '';

        foreach ($items as $item) {
            $type = $item['type'] ?? '';

            if (in_array($type, ['text', 'tag'])) {
                $text .= $item['text'] ?? '';
            } elseif (isset($item['items']) && is_array($item['items'])) {
                // Recurse into inline containers (e.g. "char" for words of Christ, etc.)
                $text .= $this->extractText($item['items']);
            }
        }

        return $text;
    }

    private function emptyPassage(string $book, int $chapter, string $translation): array
    {
        return [
            'book' => $book,
            'chapter' => $chapter,
            'translation' => $translation,
            'reference' => "{$book} {$chapter}",
            'totalChapters' => 150,
            'verses' => [],
        ];
    }
}
