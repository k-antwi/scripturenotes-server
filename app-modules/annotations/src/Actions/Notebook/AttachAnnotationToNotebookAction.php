<?php

namespace Nucleus\Annotations\Actions\Notebook;

use Nucleus\Annotations\Models\Notebook;

/**
 * Tag an annotation into a notebook (many-to-many, PRD §4.2 annotation_notebook).
 * Idempotent — calling twice doesn't create a duplicate pivot row.
 *
 * Usage:
 *   AttachAnnotationToNotebookAction::run($notebook, $annotationId);
 */
class AttachAnnotationToNotebookAction
{
    public function execute(Notebook $notebook, int $annotationId): void
    {
        $notebook->annotations()->syncWithoutDetaching([
            $annotationId => ['added_at' => now()],
        ]);
    }

    public static function run(Notebook $notebook, int $annotationId): void
    {
        app(self::class)->execute($notebook, $annotationId);
    }
}
