<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Actions\Notebook\AttachAnnotationToNotebookAction;
use Nucleus\Annotations\Actions\Notebook\CreateNotebookAction;
use Nucleus\Annotations\Models\Notebook;

class NotebookController extends Controller
{
    public function __construct(
        private readonly CreateNotebookAction $createNotebook,
        private readonly AttachAnnotationToNotebookAction $attachAnnotation,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Notebook::where('user_id', $request->user()->id)
                ->withCount('annotations')
                ->latest()
                ->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $notebook = $this->createNotebook->execute($request->user()->id, $validated);

        return response()->json($notebook, 201);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $notebook = Notebook::where('user_id', $request->user()->id)
            ->with('annotations')
            ->findOrFail($id);

        return response()->json($notebook);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $notebook = Notebook::where('user_id', $request->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $notebook->update($validated);

        return response()->json($notebook);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $notebook = Notebook::where('user_id', $request->user()->id)->findOrFail($id);

        if ($notebook->is_default) {
            return response()->json(['message' => 'The Untitled Notebook cannot be deleted.'], 403);
        }

        // Detach notes from this notebook (notes themselves are NOT deleted)
        $notebook->notes()->detach();
        $notebook->delete();

        return response()->json(null, 204);
    }

    public function addAnnotation(Request $request, int $id, int $annotationId): JsonResponse
    {
        $notebook = Notebook::where('user_id', $request->user()->id)->findOrFail($id);

        $this->attachAnnotation->execute($notebook, $annotationId);

        return response()->json(['attached' => true]);
    }

    public function removeAnnotation(Request $request, int $id, int $annotationId): JsonResponse
    {
        $notebook = Notebook::where('user_id', $request->user()->id)->findOrFail($id);
        $notebook->annotations()->detach($annotationId);

        return response()->json(['detached' => true]);
    }
}
