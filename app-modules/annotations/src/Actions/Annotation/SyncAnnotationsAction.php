<?php

namespace Nucleus\Annotations\Actions\Annotation;

use Nucleus\Annotations\Models\Annotation;

/**
 * Batch upsert annotations from the offline-first sync payload (PRD §7.2 POST /sync).
 *
 * Each mutation is: { localId, remoteId?, action, book, chapter, verse, type, data, colour, updatedAt, deletedAt }
 * Returns: [{ localId, remoteId }] so the mobile client can reconcile its local IDs.
 *
 * Can be called from:
 *   - AnnotationController (HTTP sync endpoint)
 *   - A queued Job (background bulk import)
 *   - Console commands (admin tooling)
 *
 * Usage:
 *   $results = app(SyncAnnotationsAction::class)->execute($userId, $mutations);
 *   $results = SyncAnnotationsAction::run($userId, $mutations);
 */
class SyncAnnotationsAction
{
    /**
     * @param  int    $userId
     * @param  array  $mutations  Array of mutation payloads
     * @return array  [{ localId, remoteId }]
     */
    public function execute(int $userId, array $mutations): array
    {
        $results = [];

        foreach ($mutations as $mutation) {
            $action = $mutation['action'];
            $remoteId = $mutation['remoteId'] ?? null;

            $results[] = match ($action) {
                'delete' => $this->handleDelete($userId, $mutation, $remoteId),
                'update' => $this->handleUpdate($userId, $mutation, $remoteId),
                default  => $this->handleCreate($userId, $mutation, $remoteId),
            };
        }

        return $results;
    }

    public static function run(int $userId, array $mutations): array
    {
        return app(self::class)->execute($userId, $mutations);
    }

    private function handleDelete(int $userId, array $mutation, ?int $remoteId): array
    {
        if ($remoteId) {
            Annotation::forUser($userId)
                ->where('id', $remoteId)
                ->update(['deleted_at' => now()]);
        }

        return ['localId' => $mutation['localId'], 'remoteId' => $remoteId];
    }

    private function handleUpdate(int $userId, array $mutation, ?int $remoteId): array
    {
        if ($remoteId) {
            $annotation = Annotation::forUser($userId)->find($remoteId);

            if ($annotation) {
                $annotation->update([
                    'data' => $mutation['data'] ?? $annotation->data,
                    'colour' => $mutation['colour'] ?? $annotation->colour,
                    'deleted_at' => $mutation['deletedAt'] ?? $annotation->deleted_at,
                    'updated_at' => $mutation['updatedAt'] ?? now(),
                ]);
            }
        }

        return ['localId' => $mutation['localId'], 'remoteId' => $remoteId];
    }

    private function handleCreate(int $userId, array $mutation, ?int $remoteId): array
    {
        $annotation = Annotation::create([
            'user_id' => $userId,
            'book' => strtoupper($mutation['book'] ?? ''),
            'chapter' => $mutation['chapter'] ?? 0,
            'verse' => $mutation['verse'] ?? null,
            'type' => $mutation['type'] ?? 'note',
            'data' => $mutation['data'] ?? null,
            'colour' => $mutation['colour'] ?? null,
            'is_shared' => $mutation['isShared'] ?? false,
            'deleted_at' => $mutation['deletedAt'] ?? null,
            'updated_at' => $mutation['updatedAt'] ?? now(),
        ]);

        return ['localId' => $mutation['localId'], 'remoteId' => $annotation->id];
    }
}
