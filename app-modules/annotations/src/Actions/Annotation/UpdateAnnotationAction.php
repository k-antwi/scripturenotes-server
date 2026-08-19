<?php

namespace Nucleus\Annotations\Actions\Annotation;

use Illuminate\Support\Str;
use Nucleus\Annotations\Models\Annotation;

/**
 * Update an existing annotation (colour change, stroke data edit, sharing toggle).
 *
 * Usage:
 *   $annotation = app(UpdateAnnotationAction::class)->execute($annotation, $changes);
 */
class UpdateAnnotationAction
{
    public function execute(Annotation $annotation, array $changes): Annotation
    {
        if (isset($changes['is_shared'])) {
            $changes['share_token'] = $changes['is_shared']
                ? ($annotation->share_token ?? Str::random(32))
                : null;
        }

        $annotation->update($changes);

        return $annotation->refresh();
    }

    public static function run(Annotation $annotation, array $changes): Annotation
    {
        return app(self::class)->execute($annotation, $changes);
    }
}
