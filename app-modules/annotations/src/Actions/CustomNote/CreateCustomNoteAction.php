<?php

namespace Nucleus\Annotations\Actions\CustomNote;

use Nucleus\Annotations\Models\CustomNote;

/**
 * Create a user-authored commentary note (PRD §4.2 custom_notes, §5.3).
 *
 * Usage:
 *   $note = CreateCustomNoteAction::run($userId, $data);
 */
class CreateCustomNoteAction
{
    public function execute(int $userId, array $data): CustomNote
    {
        return CustomNote::create([
            'user_id' => $userId,
            'book' => strtoupper($data['book']),
            'chapter' => $data['chapter'],
            'verse' => $data['verse'] ?? null,
            'title' => $data['title'] ?? null,
            'body' => $data['body'],
        ]);
    }

    public static function run(int $userId, array $data): CustomNote
    {
        return app(self::class)->execute($userId, $data);
    }
}
