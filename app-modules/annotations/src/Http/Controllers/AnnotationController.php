<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Actions\Annotation\CreateAnnotationAction;
use Nucleus\Annotations\Actions\Annotation\SoftDeleteAnnotationAction;
use Nucleus\Annotations\Actions\Annotation\SyncAnnotationsAction;
use Nucleus\Annotations\Actions\Annotation\UpdateAnnotationAction;
use Nucleus\Annotations\Models\Annotation;

/**
 * Annotation Controller — PRD §7.2 (thin controller, delegates to Actions)
 *
 * GET    /api/annotations
 * POST   /api/annotations
 * PUT    /api/annotations/{id}
 * DELETE /api/annotations/{id}
 * POST   /api/annotations/sync
 */
class AnnotationController extends Controller
{
    public function __construct(
        private readonly CreateAnnotationAction $createAnnotation,
        private readonly UpdateAnnotationAction $updateAnnotation,
        private readonly SoftDeleteAnnotationAction $softDeleteAnnotation,
        private readonly SyncAnnotationsAction $syncAnnotations,
    ) {}

    /** List annotations for the authenticated user, filterable by book/chapter/type/colour. */
    public function index(Request $request): JsonResponse
    {
        $query = Annotation::forUser($request->user()->id)->active();

        if ($request->filled('book')) {
            $query->where('book', strtoupper($request->book));
        }
        if ($request->filled('chapter')) {
            $query->where('chapter', (int) $request->chapter);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('colour')) {
            $query->where('colour', $request->colour);
        }

        return response()->json($query->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book' => 'required|string|max:10',
            'chapter' => 'required|integer|min:1',
            'verse' => 'nullable|integer|min:1',
            'type' => 'required|string|in:highlight,pen,note,underline,shape,custom_note',
            'data' => 'nullable|array',
            'colour' => 'nullable|string|max:20',
            'is_shared' => 'nullable|boolean',
        ]);

        $annotation = $this->createAnnotation->execute($request->user()->id, $validated);

        return response()->json($annotation, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $annotation = Annotation::forUser($request->user()->id)->active()->findOrFail($id);

        $validated = $request->validate([
            'data' => 'nullable|array',
            'colour' => 'nullable|string|max:20',
            'is_shared' => 'nullable|boolean',
            'verse' => 'nullable|integer|min:1',
        ]);

        $annotation = $this->updateAnnotation->execute($annotation, $validated);

        return response()->json($annotation);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $annotation = Annotation::forUser($request->user()->id)->active()->findOrFail($id);

        $this->softDeleteAnnotation->execute($annotation);

        return response()->json(['deleted' => true]);
    }

    /** Batch upsert for offline-first sync (PRD §7.2 POST /sync). */
    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'mutations' => 'required|array',
            'mutations.*.localId' => 'nullable',
            'mutations.*.remoteId' => 'nullable|integer',
            'mutations.*.action' => 'required|string|in:create,update,delete',
            'mutations.*.book' => 'required_if:mutations.*.action,create|string|max:10',
            'mutations.*.chapter' => 'required_if:mutations.*.action,create|integer',
            'mutations.*.type' => 'required_if:mutations.*.action,create|string',
            'mutations.*.data' => 'nullable|array',
            'mutations.*.colour' => 'nullable|string|max:20',
            'mutations.*.updatedAt' => 'nullable|date',
            'mutations.*.deletedAt' => 'nullable|date',
        ]);

        $results = $this->syncAnnotations->execute($request->user()->id, $request->mutations);

        return response()->json(['results' => $results]);
    }
}
