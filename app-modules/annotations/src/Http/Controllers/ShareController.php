<?php

namespace Nucleus\Annotations\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Nucleus\Annotations\Actions\Annotation\ShareAnnotationAction;
use Nucleus\Annotations\Models\Annotation;

/**
 * Annotation Sharing (PRD §11.2)
 *
 * POST /api/annotations/{id}/share  — authenticated; generates share token
 * GET  /api/shared/{token}          — public; returns the live annotation
 */
class ShareController extends Controller
{
    public function __construct(
        private readonly ShareAnnotationAction $shareAnnotation
    ) {}

    /**
     * Generate (or return the existing) share token for the authenticated
     * user's annotation, making it publicly accessible via a link.
     */
    public function share(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $annotation = Annotation::forUser($user->id)
            ->active()
            ->findOrFail($id);

        $annotation = $this->shareAnnotation->execute($annotation);

        return response()->json([
            'share_token' => $annotation->share_token,
            'share_url'   => url("/api/shared/{$annotation->share_token}"),
            'annotation'  => $annotation,
        ]);
    }

    /**
     * Revoke sharing for an annotation (sets is_shared = false).
     * The share_token is preserved so the same URL can be re-activated later.
     */
    public function revoke(Request $request, int $id): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $annotation = Annotation::forUser($user->id)
            ->active()
            ->findOrFail($id);

        $annotation->is_shared = false;
        $annotation->save();

        return response()->json(['revoked' => true]);
    }

    /**
     * Public endpoint — returns the live annotation for any visitor who
     * knows the share token (PRD §11.2: "all users it was shared with see
     * the update automatically").
     */
    public function show(string $token): JsonResponse
    {
        $annotation = Annotation::where('share_token', $token)
            ->where('is_shared', true)
            ->whereNull('deleted_at')
            ->firstOrFail();

        return response()->json($annotation);
    }
}
