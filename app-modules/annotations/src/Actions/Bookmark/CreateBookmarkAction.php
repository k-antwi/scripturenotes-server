<?php

namespace Nucleus\Annotations\Actions\Bookmark;

use Nucleus\Annotations\Models\Bookmark;

/**
 * Create (or find existing) a bookmark — idempotent.
 *
 * Usage:
 *   $bookmark = CreateBookmarkAction::run($userId, $data);
 */
class CreateBookmarkAction
{
    /**
     * @param  array{ book: string, chapter: int, verse?: int|null, label?: string|null } $data
     */
    public function execute(int $userId, array $data): Bookmark
    {
        return Bookmark::firstOrCreate(
            [
                'user_id' => $userId,
                'book' => strtoupper($data['book']),
                'chapter' => $data['chapter'],
                'verse' => $data['verse'] ?? null,
            ],
            ['label' => $data['label'] ?? null]
        );
    }

    public static function run(int $userId, array $data): Bookmark
    {
        return app(self::class)->execute($userId, $data);
    }
}
