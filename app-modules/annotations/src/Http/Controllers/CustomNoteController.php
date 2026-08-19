<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Actions\CustomNote\CreateCustomNoteAction;
use Nucleus\Annotations\Models\CustomNote;

class CustomNoteController extends Controller
{
    public function __construct(private readonly CreateCustomNoteAction $createCustomNote) {}

    public function index(Request $request): JsonResponse
    {
        $query = CustomNote::where('user_id', $request->user()->id);

        if ($request->filled('book')) {
            $query->where('book', strtoupper($request->book));
        }
        if ($request->filled('chapter')) {
            $query->where('chapter', (int) $request->chapter);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book' => 'required|string|max:10',
            'chapter' => 'required|integer|min:1',
            'verse' => 'nullable|integer|min:1',
            'title' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $note = $this->createCustomNote->execute($request->user()->id, $validated);

        return response()->json($note, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $note = CustomNote::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'body' => 'sometimes|required|string',
        ]);

        $note->update($validated);

        return response()->json($note);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        CustomNote::where('user_id', $request->user()->id)->findOrFail($id)->delete();

        return response()->json(['deleted' => true]);
    }
}
