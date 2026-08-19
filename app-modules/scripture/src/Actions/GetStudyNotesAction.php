<?php

namespace Nucleus\Scripture\Actions;

use Nucleus\Scripture\Services\ScriptureService;

/**
 * Fetch study notes for a chapter (ESV Global SB or user-added).
 *
 * Usage:
 *   $notes = app(GetStudyNotesAction::class)->execute('PRO', 19, 'ESV');
 *   $notes = GetStudyNotesAction::run('PRO', 19, 'ESV');
 */
class GetStudyNotesAction
{
    public function __construct(private readonly ScriptureService $scripture) {}

    public function execute(string $book, int $chapter, string $translation = 'ESV'): array
    {
        return $this->scripture->getStudyNotes($book, $chapter, $translation);
    }

    public static function run(string $book, int $chapter, string $translation = 'ESV'): array
    {
        return app(self::class)->execute($book, $chapter, $translation);
    }
}
