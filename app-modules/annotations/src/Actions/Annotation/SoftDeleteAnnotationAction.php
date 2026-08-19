<?php

namespace Nucleus\Annotations\Actions\Annotation;

use Nucleus\Annotations\Models\Annotation;

/**
 * Soft-delete an annotation (PRD §7.2 — DELETE is always soft).
 * Can be called from the API controller or a background cleanup job.
 *
 * Usage:
 *   SoftDeleteAnnotationAction::run($annotation);
 */
class SoftDeleteAnnotationAction
{
    public function execute(Annotation $annotation): Annotation
    {
        $annotation->update(['deleted_at' => now()]);

        return $annotation;
    }

    public static function run(Annotation $annotation): Annotation
    {
        return app(self::class)->execute($annotation);
    }
}
