<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Models\Note;
use Nucleus\Annotations\Models\Notebook;

/**
 * Notes Controller — PRD §5.3, §6.5, §7.2
 *
 * GET    /api/notes
 * POST   /api/notes
 * GET    /api/notes/{id}
 * PUT    /api/notes/{id}
 * DELETE /api/notes/{id}
 * GET    /api/notes/passage/{book}/{chapter}
 * POST   /api/notes/sync
 * GET    /api/notebooks/{id}/notes
 */
class NoteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Note::where('user_id', $request->user()->id);

        if ($request->filled('book')) {
            $query->where('book', strtoupper($request->book));
        }
        if ($request->filled('chapter')) {
            $query->where('chapter', (int) $request->chapter);
        }
        if ($request->filled('notebook_id')) {
            $query->whereHas('notebooks', fn ($q) => $q->where('notebooks.id', (int) $request->notebook_id));
        }

        return response()->json($query->with('notebooks')->latest()->paginate(50));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book'          => 'nullable|string|max:10',
            'chapter'       => 'nullable|integer|min:1',
            'verse'         => 'nullable|integer|min:1',
            'char_start'    => 'nullable|integer|min:0',
            'char_end'      => 'nullable|integer|min:0',
            'title'         => 'nullable|string|max:255',
            'body'          => 'required|string',
            'notebook_ids'  => 'nullable|array',
            'notebook_ids.*' => 'integer',
        ]);

        $note = Note::create([
            'user_id'    => $request->user()->id,
            'book'       => $validated['book'] ? strtoupper($validated['book']) : null,
            'chapter'    => $validated['chapter'] ?? null,
            'verse'      => $validated['verse'] ?? null,
            'char_start' => $validated['char_start'] ?? null,
            'char_end'   => $validated['char_end'] ?? null,
            'title'      => $validated['title'] ?? null,
            'body'       => $validated['body'],
        ]);

        $notebookIds = collect($validated['notebook_ids'] ?? []);

        // Always include the user's Untitled Notebook
        $default = Notebook::defaultForUser($request->user()->id);
        $notebookIds = $notebookIds->push($default->id)->unique()->values();

        // Verify all notebook_ids belong to this user before attaching
        $ownedIds = Notebook::where('user_id', $request->user()->id)
            ->whereIn('id', $notebookIds)
            ->pluck('id');

        $note->notebooks()->sync($ownedIds->mapWithKeys(fn ($id) => [$id => ['added_at' => now()]]));

        return response()->json($note->load('notebooks'), 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $note = Note::where('user_id', $request->user()->id)
            ->with('notebooks')
            ->findOrFail($id);

        return response()->json($note->append('anchor_type'));
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $note = Note::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'book'          => 'sometimes|nullable|string|max:10',
            'chapter'       => 'sometimes|nullable|integer|min:1',
            'verse'         => 'sometimes|nullable|integer|min:1',
            'char_start'    => 'sometimes|nullable|integer|min:0',
            'char_end'      => 'sometimes|nullable|integer|min:0',
            'title'         => 'sometimes|nullable|string|max:255',
            'body'          => 'sometimes|required|string',
            'notebook_ids'  => 'sometimes|nullable|array',
            'notebook_ids.*' => 'integer',
        ]);

        if (isset($validated['book'])) {
            $validated['book'] = $validated['book'] ? strtoupper($validated['book']) : null;
        }

        $note->update($validated);

        if (array_key_exists('notebook_ids', $validated)) {
            $notebookIds = collect($validated['notebook_ids'] ?? []);
            $default = Notebook::defaultForUser($request->user()->id);
            $notebookIds = $notebookIds->push($default->id)->unique()->values();

            $ownedIds = Notebook::where('user_id', $request->user()->id)
                ->whereIn('id', $notebookIds)
                ->pluck('id');

            $note->notebooks()->sync($ownedIds->mapWithKeys(fn ($id) => [$id => ['added_at' => now()]]));
        }

        return response()->json($note->load('notebooks')->append('anchor_type'));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        Note::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(null, 204);
    }

    /** All notes for a specific passage — used for in-context rendering in the reader. */
    public function forPassage(Request $request, string $book, int $chapter): JsonResponse
    {
        $notes = Note::where('user_id', $request->user()->id)
            ->forPassage($book, $chapter)
            ->get()
            ->append('anchor_type');

        return response()->json($notes);
    }

    /** Batch upsert for offline sync — mirrors /api/annotations/sync pattern. */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'mutations'              => 'required|array',
            'mutations.*.localId'   => 'nullable',
            'mutations.*.remoteId'  => 'nullable|integer',
            'mutations.*.action'    => 'required|string|in:create,update,delete',
            'mutations.*.body'      => 'required_if:mutations.*.action,create|string',
            'mutations.*.updatedAt' => 'nullable|date',
        ]);

        $results = [];
        $userId = $request->user()->id;

        foreach ($request->mutations as $mutation) {
            $action   = $mutation['action'];
            $localId  = $mutation['localId'] ?? null;
            $remoteId = $mutation['remoteId'] ?? null;

            if ($action === 'create') {
                $note = Note::create([
                    'user_id'    => $userId,
                    'book'       => isset($mutation['book']) ? strtoupper($mutation['book']) : null,
                    'chapter'    => $mutation['chapter'] ?? null,
                    'verse'      => $mutation['verse'] ?? null,
                    'char_start' => $mutation['char_start'] ?? null,
                    'char_end'   => $mutation['char_end'] ?? null,
                    'title'      => $mutation['title'] ?? null,
                    'body'       => $mutation['body'],
                ]);

                $default = Notebook::defaultForUser($userId);
                $note->notebooks()->syncWithoutDetaching([$default->id => ['added_at' => now()]]);

                $results[] = ['localId' => $localId, 'remoteId' => $note->id];
            } elseif ($action === 'update' && $remoteId) {
                $note = Note::where('user_id', $userId)->find($remoteId);
                if ($note) {
                    $note->update(array_filter([
                        'book'       => isset($mutation['book']) ? strtoupper($mutation['book']) : null,
                        'chapter'    => $mutation['chapter'] ?? null,
                        'verse'      => $mutation['verse'] ?? null,
                        'char_start' => $mutation['char_start'] ?? null,
                        'char_end'   => $mutation['char_end'] ?? null,
                        'title'      => $mutation['title'] ?? null,
                        'body'       => $mutation['body'] ?? null,
                    ], fn ($v) => $v !== null));
                }
                $results[] = ['localId' => $localId, 'remoteId' => $remoteId];
            } elseif ($action === 'delete' && $remoteId) {
                Note::where('user_id', $userId)->find($remoteId)?->delete();
                $results[] = ['localId' => $localId, 'remoteId' => $remoteId];
            }
        }

        return response()->json(['results' => $results]);
    }

    /** Notes belonging to a specific notebook (paginated). */
    public function notebookNotes(Request $request, int $notebookId): JsonResponse
    {
        $notebook = Notebook::where('user_id', $request->user()->id)->findOrFail($notebookId);

        $notes = $notebook->notes()
            ->withCount([])
            ->latest()
            ->paginate(50);

        return response()->json($notes);
    }
}
