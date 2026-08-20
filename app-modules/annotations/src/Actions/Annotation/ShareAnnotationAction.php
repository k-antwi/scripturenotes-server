<?php

namespace Nucleus\Annotations\Actions\Annotation;

use Illuminate\Support\Str;
use Nucleus\Annotations\Models\Annotation;

/**
 * Generates (or returns) a public share token for an annotation (PRD §11.2).
 *
 * When the original author subsequently edits the annotation, the shared
 * link reflects the update automatically because it resolves via the
 * annotation's primary key — not a snapshot copy.
 */
class ShareAnnotationAction
{
    public function execute(Annotation $annotation): Annotation
    {
        if (! $annotation->share_token) {
            $annotation->share_token = Str::uuid()->toString();
        }

        $annotation->is_shared = true;
        $annotation->save();

        return $annotation;
    }
}
