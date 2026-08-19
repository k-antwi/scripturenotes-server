<?php

namespace Nucleus\Annotations\Actions\Annotation;

use Illuminate\Support\Str;
use Nucleus\Annotations\Models\Annotation;

/**
 * Create a new annotation (highlight, pen, underline, shape, note, custom_note).
 *
 * Usage:
 *   $annotation = app(CreateAnnotationAction::class)->execute($userId, $data);
 *   $annotation = CreateAnnotationAction::run($userId, $data);
 */
class CreateAnnotationAction
{
    /**
     * @param  int    $userId
     * @param  array{
     *   book: string,
     *   chapter: int,
     *   verse?: int|null,
     *   type: string,
     *   data?: array|null,
     *   colour?: string|null,
     *   is_shared?: bool,
     * } $data
     */
    public function execute(int $userId, array $data): Annotation
    {
        $isShared = $data['is_shared'] ?? false;

        return Annotation::create([
            'user_id' => $userId,
            'book' => strtoupper($data['book']),
            'chapter' => $data['chapter'],
            'verse' => $data['verse'] ?? null,
            'type' => $data['type'],
            'data' => $data['data'] ?? null,
            'colour' => $data['colour'] ?? null,
            'is_shared' => $isShared,
            'share_token' => $isShared ? Str::random(32) : null,
        ]);
    }

    public static function run(int $userId, array $data): Annotation
    {
        return app(self::class)->execute($userId, $data);
    }
}
