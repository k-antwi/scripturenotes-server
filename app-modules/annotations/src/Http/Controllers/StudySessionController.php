<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Actions\StudySession\RecordStudySessionAction;
use Nucleus\Annotations\Models\StudySession;

class StudySessionController extends Controller
{
    public function __construct(private readonly RecordStudySessionAction $recordSession) {}

    public function index(Request $request): JsonResponse
    {
        $sessions = StudySession::where('user_id', $request->user()->id)
            ->orderBy('started_at', 'desc')
            ->limit(100)
            ->get();

        return response()->json($sessions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'passage_ref' => 'nullable|string|max:50',
            'started_at' => 'nullable|date',
        ]);

        $startedAt = isset($validated['started_at'])
            ? \Carbon\Carbon::parse($validated['started_at'])
            : null;

        $session = $this->recordSession->execute(
            $request->user()->id,
            $validated['passage_ref'] ?? null,
            $startedAt,
        );

        return response()->json($session, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $session = StudySession::where('user_id', $request->user()->id)->findOrFail($id);
        $session->update(['last_active_at' => now()]);

        return response()->json($session);
    }
}
