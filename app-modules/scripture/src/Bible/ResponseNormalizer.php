<?php

namespace Nucleus\Scripture\Bible;

/**
 * ResponseNormalizer — translates each provider's raw response into
 * the single unified shape consumed by the mobile client.
 *
 * Unified shape:
 * {
 *   "data": {
 *     "reference":  "John 3:16",
 *     "version":    "NIV",
 *     "verses": [{ "book", "chapter", "verse", "verseId", "text" }],
 *     "html":       "<p>...</p>",     // optional
 *     "copyright":  "© 2011 Biblica" // optional
 *   },
 *   "meta": { "provider": "api_bible", "cached": false }
 * }
 *
 * VerseId encoding: (bookNumber * 1_000_000) + (chapter * 1_000) + verse
 *   John 3:16      → book 43 → 43003016
 *   Genesis 1:1    → book 1  → 1001001
 *   Revelation 22:21 → book 66 → 66022021
 */
class ResponseNormalizer
{
    /** Book abbreviation → canonical integer (1-based) */
    private const BOOK_NUMBERS = [
        'GEN' => 1,  'EXO' => 2,  'LEV' => 3,  'NUM' => 4,  'DEU' => 5,
        'JOS' => 6,  'JDG' => 7,  'RUT' => 8,  '1SA' => 9,  '2SA' => 10,
        '1KI' => 11, '2KI' => 12, '1CH' => 13, '2CH' => 14, 'EZR' => 15,
        'NEH' => 16, 'EST' => 17, 'JOB' => 18, 'PSA' => 19, 'PRO' => 20,
        'ECC' => 21, 'SNG' => 22, 'ISA' => 23, 'JER' => 24, 'LAM' => 25,
        'EZK' => 26, 'DAN' => 27, 'HOS' => 28, 'JOL' => 29, 'AMO' => 30,
        'OBA' => 31, 'JON' => 32, 'MIC' => 33, 'NAM' => 34, 'HAB' => 35,
        'ZEP' => 36, 'HAG' => 37, 'ZEC' => 38, 'MAL' => 39,
        'MAT' => 40, 'MRK' => 41, 'LUK' => 42, 'JHN' => 43, 'ACT' => 44,
        'ROM' => 45, '1CO' => 46, '2CO' => 47, 'GAL' => 48, 'EPH' => 49,
        'PHP' => 50, 'COL' => 51, '1TH' => 52, '2TH' => 53, '1TI' => 54,
        '2TI' => 55, 'TIT' => 56, 'PHM' => 57, 'HEB' => 58, 'JAS' => 59,
        '1PE' => 60, '2PE' => 61, '1JN' => 62, '2JN' => 63, '3JN' => 64,
        'JUD' => 65, 'REV' => 66,
    ];

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

    // ─── Public entry points ──────────────────────────────────────────────────

    /**
     * Normalize an API.Bible chapter/passage response.
     */
    public function fromApiBible(array $raw, string $version, string $providerName, bool $cached = false): array
    {
        $data = $raw['data'] ?? $raw;
        $verses = $this->extractApiBibleVerses($data, $data['bookId'] ?? '', (int) ($data['number'] ?? $data['chapterNum'] ?? 0));

        $content = $data['content'] ?? null;

        return $this->buildEnvelope(
            reference: $data['reference'] ?? '',
            version: $version,
            verses: $verses,
            html: is_string($content) ? $content : null,
            copyright: $data['copyright'] ?? null,
            provider: $providerName,
            cached: $cached,
        );
    }

    /**
     * Normalize a Free Use Bible API (bible-api.com) response.
     */
    public function fromFreeUse(array $raw, string $version, bool $cached = false): array
    {
        $rawVerses = $raw['verses'] ?? [];
        $verses = [];

        foreach ($rawVerses as $v) {
            $bookAbbr = strtoupper($v['book_id'] ?? '');
            $chapter  = (int) ($v['chapter'] ?? 0);
            $verseNum = (int) ($v['verse'] ?? 0);

            $verses[] = [
                'book'    => $bookAbbr,
                'chapter' => $chapter,
                'verse'   => $verseNum,
                'verseId' => $this->encodeVerseId($bookAbbr, $chapter, $verseNum),
                'text'    => trim($v['text'] ?? ''),
            ];
        }

        return $this->buildEnvelope(
            reference: $raw['reference'] ?? '',
            version: strtoupper($raw['translation_id'] ?? $version),
            verses: $verses,
            html: null,
            copyright: $raw['translation_note'] ?? null,
            provider: 'free_use',
            cached: $cached,
        );
    }

    /**
     * Normalize a Bolls search result.
     */
    public function fromBollsSearch(array $raw, string $version, bool $cached = false): array
    {
        $results = is_array($raw) ? $raw : [];
        $verses  = [];

        foreach ($results as $item) {
            $bookAbbr = strtoupper($item['book'] ?? '');
            $chapter  = (int) ($item['chapter'] ?? 0);
            $verseNum = (int) ($item['verse'] ?? 0);

            $verses[] = [
                'book'    => $bookAbbr,
                'chapter' => $chapter,
                'verse'   => $verseNum,
                'verseId' => $this->encodeVerseId($bookAbbr, $chapter, $verseNum),
                'text'    => trim($item['text'] ?? ''),
            ];
        }

        return $this->buildEnvelope(
            reference: "Search results — {$version}",
            version: $version,
            verses: $verses,
            html: null,
            copyright: null,
            provider: 'bolls',
            cached: $cached,
        );
    }

    /**
     * Normalize a YouVersion verse-of-the-day response.
     */
    public function fromYouVersionVotd(array $raw, bool $cached = false): array
    {
        $verse = $raw['verse'] ?? $raw;
        $refs  = $verse['refs'] ?? [];
        $verses = [];

        foreach ($refs as $ref) {
            $bookAbbr = strtoupper($ref['human'] ?? '');
            $verses[] = [
                'book'    => $bookAbbr,
                'chapter' => 0,
                'verse'   => 0,
                'verseId' => 0,
                'text'    => trim($ref['text'] ?? $verse['text'] ?? ''),
            ];
        }

        return $this->buildEnvelope(
            reference: $verse['human'] ?? 'Verse of the Day',
            version: strtoupper($verse['version'] ?? 'NIV'),
            verses: $verses,
            html: null,
            copyright: null,
            provider: 'youversion',
            cached: $cached,
        );
    }

    /**
     * Normalize a BibleBrain audio response.
     */
    public function fromBibleBrainAudio(array $raw, string $reference, string $version, bool $cached = false): array
    {
        $streamingUrls = [];
        foreach ($raw['data'] ?? $raw as $file) {
            $streamingUrls[] = [
                'url'    => $file['path'] ?? $file['url'] ?? '',
                'format' => $file['filetype'] ?? 'mp3',
                'book'   => strtoupper($file['book_id'] ?? ''),
                'chapter' => (int) ($file['chapter_start'] ?? 0),
            ];
        }

        return [
            'data' => [
                'reference' => $reference,
                'version'   => strtoupper($version),
                'audio'     => $streamingUrls,
            ],
            'meta' => ['provider' => 'bible_brain', 'cached' => $cached],
        ];
    }

    /**
     * Normalize a versions/translations listing.
     */
    public function fromVersionsList(array $raw, string $provider, bool $cached = false): array
    {
        $items = $raw['data'] ?? $raw;
        $versions = [];

        foreach ((array) $items as $v) {
            $versions[] = [
                'id'           => $v['id'] ?? $v['abbreviation'] ?? '',
                'name'         => $v['name'] ?? $v['nameLocal'] ?? '',
                'abbreviation' => $v['abbreviation'] ?? $v['id'] ?? '',
                'language'     => $v['language']['id'] ?? $v['language'] ?? '',
            ];
        }

        return [
            'data' => $versions,
            'meta' => ['provider' => $provider, 'cached' => $cached],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function encodeVerseId(string $bookAbbr, int $chapter, int $verse): int
    {
        $bookNum = self::BOOK_NUMBERS[strtoupper($bookAbbr)] ?? 0;

        return ($bookNum * 1_000_000) + ($chapter * 1_000) + $verse;
    }

    private function buildEnvelope(
        string $reference,
        string $version,
        array $verses,
        ?string $html,
        ?string $copyright,
        string $provider,
        bool $cached,
    ): array {
        $payload = [
            'reference' => $reference,
            'version'   => strtoupper($version),
            'verses'    => $verses,
        ];

        if ($html !== null) {
            $payload['html'] = $html;
        }

        if ($copyright !== null) {
            $payload['copyright'] = $copyright;
        }

        return [
            'data' => $payload,
            'meta' => ['provider' => $provider, 'cached' => $cached],
        ];
    }

    /**
     * Walk API.Bible's nested content tree and extract verse nodes.
     */
    private function extractApiBibleVerses(array $data, string $bookAbbr, int $chapterNum): array
    {
        $verses = [];

        foreach ($data['content'] ?? [] as $block) {
            $this->collectVerseSpans($block, $bookAbbr, $chapterNum, $verses);
        }

        ksort($verses);

        return array_values($verses);
    }

    private function collectVerseSpans(array $block, string $bookAbbr, int $chapterNum, array &$verses): void
    {
        if (($block['name'] ?? '') === 'verse-span') {
            $verseId  = $block['attrs']['verseId'] ?? '';
            $parts    = explode('.', $verseId);
            $verseNum = (int) end($parts);

            if ($verseNum > 0) {
                $text = $this->textFromVerseSpan($block['items'] ?? []);

                if (isset($verses[$verseNum])) {
                    if ($text !== '') {
                        $verses[$verseNum]['text'] .= ($verses[$verseNum]['text'] !== '' ? ' ' : '') . $text;
                    }
                } else {
                    $verses[$verseNum] = [
                        'book'    => $bookAbbr,
                        'chapter' => $chapterNum,
                        'verse'   => $verseNum,
                        'verseId' => $this->encodeVerseId($bookAbbr, $chapterNum, $verseNum),
                        'text'    => $text,
                    ];
                }
            }

            return;
        }

        foreach ($block['items'] ?? [] as $item) {
            if (is_array($item)) {
                $this->collectVerseSpans($item, $bookAbbr, $chapterNum, $verses);
            }
        }
    }

    private function textFromVerseSpan(array $items): string
    {
        $text = '';

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }
            if (($item['name'] ?? '') === 'verse') {
                continue; // verse number marker only — skip
            }
            if (($item['type'] ?? '') === 'text') {
                $text .= $item['text'] ?? '';
            } elseif (isset($item['items'])) {
                $text .= $this->textFromVerseSpan($item['items']);
            }
        }

        return trim($text);
    }
}
