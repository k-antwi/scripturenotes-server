<?php

namespace Nucleus\Scripture\Http\Controllers;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Scripture\Actions\GetPassageAction;
use Nucleus\Scripture\Actions\GetStudyNotesAction;

/**
 * Passage Controller — cache proxy to Bible API (PRD §7.3).
 * Delegates all logic to Actions so the same operations can run from Jobs/Commands.
 *
 * GET /api/passages/{book}/{chapter}               → passage JSON
 * GET /api/passages/{book}/{chapter}/notes         → study notes JSON
 */
class PassageController extends Controller
{
    public function __construct(
        private readonly GetPassageAction $getPassage,
        private readonly GetStudyNotesAction $getStudyNotes,
    ) {}

    /**
     * GET /api/passages/{book}/{chapter}?translation=NKJV
     */
    public function show(Request $request, string $book, int $chapter): JsonResponse
    {
        $translation = strtoupper($request->query('translation', 'KJV'));

        try {
            $passage = $this->getPassage->execute($book, $chapter, $translation);
        } catch (GuzzleException $e) {
            return response()->json(
                ['error' => 'Bible API unavailable. Check API_BIBLE_KEY or try again later.'],
                502
            );
        }

        return response()->json($passage);
    }

    /**
     * GET /api/passages/{book}/{chapter}/notes?translation=ESV
     */
    public function notes(Request $request, string $book, int $chapter): JsonResponse
    {
        $translation = strtoupper($request->query('translation', 'ESV'));

        $notes = $this->getStudyNotes->execute($book, $chapter, $translation);

        return response()->json($notes);
    }
}
