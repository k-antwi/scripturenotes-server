<?php

namespace Nucleus\Annotations\Actions\StudySession;

use Nucleus\Annotations\Models\StudySession;

/**
 * Record or start a study session (PRD §5.5).
 * Called from the HTTP controller, a queued job, or a Livewire heartbeat.
 *
 * Usage:
 *   $session = RecordStudySessionAction::run($userId, 'PRO 19');
 */
class RecordStudySessionAction
{
    public function execute(int $userId, ?string $passageRef = null, ?\DateTimeInterface $startedAt = null): StudySession
    {
        return StudySession::create([
            'user_id' => $userId,
            'passage_ref' => $passageRef,
            'started_at' => $startedAt ?? now(),
            'last_active_at' => now(),
        ]);
    }

    public static function run(int $userId, ?string $passageRef = null, ?\DateTimeInterface $startedAt = null): StudySession
    {
        return app(self::class)->execute($userId, $passageRef, $startedAt);
    }
}
