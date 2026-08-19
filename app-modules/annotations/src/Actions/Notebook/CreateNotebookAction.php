<?php

namespace Nucleus\Annotations\Actions\Notebook;

use Nucleus\Annotations\Models\Notebook;

/**
 * Create a new notebook (user-created annotation collection, PRD §4.2).
 *
 * Usage:
 *   $notebook = CreateNotebookAction::run($userId, ['title' => 'Sunday Sermon Prep']);
 */
class CreateNotebookAction
{
    public function execute(int $userId, array $data): Notebook
    {
        return Notebook::create([
            'user_id' => $userId,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public static function run(int $userId, array $data): Notebook
    {
        return app(self::class)->execute($userId, $data);
    }
}
