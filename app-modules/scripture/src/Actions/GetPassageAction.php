<?php

namespace Nucleus\Scripture\Actions;

use Nucleus\Scripture\Models\Passage;
use Nucleus\Scripture\Services\ScriptureService;

/**
 * Get (or cache-fetch) a scripture passage.
 *
 * Usage:
 *   $passage = app(GetPassageAction::class)->execute('PRO', 19, 'NKJV');
 *   $passage = GetPassageAction::run('PRO', 19, 'NKJV'); // static helper
 */
class GetPassageAction
{
    public function __construct(private readonly ScriptureService $scripture) {}

    /**
     * @return array{book: string, chapter: int, translation: string, reference: string, verses: array, totalChapters: int}
     */
    public function execute(string $book, int $chapter, string $translation = 'KJV'): array
    {
        return $this->scripture->getPassage($book, $chapter, $translation);
    }

    public static function run(string $book, int $chapter, string $translation = 'KJV'): array
    {
        return app(self::class)->execute($book, $chapter, $translation);
    }
}
