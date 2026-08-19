<?php

namespace Nucleus\Annotations\Actions\Bookmark;

use Nucleus\Annotations\Models\Bookmark;

/**
 * Hard-delete a bookmark (bookmarks are not soft-deleted — PRD §7.4).
 *
 * Usage:
 *   DeleteBookmarkAction::run($bookmark);
 */
class DeleteBookmarkAction
{
    public function execute(Bookmark $bookmark): void
    {
        $bookmark->delete();
    }

    public static function run(Bookmark $bookmark): void
    {
        app(self::class)->execute($bookmark);
    }
}
