<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Actions\Bookmark\CreateBookmarkAction;
use Nucleus\Annotations\Actions\Bookmark\DeleteBookmarkAction;
use Nucleus\Annotations\Models\Bookmark;

class BookmarkController extends Controller
{
    public function __construct(
        private readonly CreateBookmarkAction $createBookmark,
        private readonly DeleteBookmarkAction $deleteBookmark,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            Bookmark::where('user_id', $request->user()->id)->latest()->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book' => 'required|string|max:10',
            'chapter' => 'required|integer|min:1',
            'verse' => 'nullable|integer|min:1',
            'label' => 'nullable|string|max:255',
        ]);

        $bookmark = $this->createBookmark->execute($request->user()->id, $validated);

        return response()->json($bookmark, 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $bookmark = Bookmark::where('user_id', $request->user()->id)->findOrFail($id);

        $this->deleteBookmark->execute($bookmark);

        return response()->json(['deleted' => true]);
    }
}
