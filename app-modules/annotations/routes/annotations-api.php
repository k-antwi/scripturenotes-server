<?php

use Illuminate\Support\Facades\Route;
use Nucleus\Annotations\Http\Controllers\AnnotationController;
use Nucleus\Annotations\Http\Controllers\BookmarkController;
use Nucleus\Annotations\Http\Controllers\CustomNoteController;
use Nucleus\Annotations\Http\Controllers\NoteController;
use Nucleus\Annotations\Http\Controllers\NotebookController;
use Nucleus\Annotations\Http\Controllers\ShareController;
use Nucleus\Annotations\Http\Controllers\StudySessionController;

/*
|--------------------------------------------------------------------------
| Annotations API Routes (PRD §7.2 – §7.4)
|--------------------------------------------------------------------------
| All routes require authentication.  Web builds use Sanctum session cookies;
| Capacitor (native) builds send a Bearer token (PRD §3 Authentication).
*/

Route::middleware(['auth:api'])->prefix('api')->group(function () {

    // ── §7.2 Annotations ────────────────────────────────────────────────────
    // Sync route must be registered BEFORE the {id} route to avoid conflict.
    Route::post('annotations/sync', [AnnotationController::class, 'sync'])
        ->name('annotations.sync');

    Route::get('annotations', [AnnotationController::class, 'index'])
        ->name('annotations.index');

    Route::post('annotations', [AnnotationController::class, 'store'])
        ->name('annotations.store');

    Route::put('annotations/{id}', [AnnotationController::class, 'update'])
        ->name('annotations.update');

    Route::delete('annotations/{id}', [AnnotationController::class, 'destroy'])
        ->name('annotations.destroy');

    // ── §11.2 Annotation Sharing (authenticated) ─────────────────────────────
    Route::post('annotations/{id}/share', [ShareController::class, 'share'])
        ->name('annotations.share');

    Route::delete('annotations/{id}/share', [ShareController::class, 'revoke'])
        ->name('annotations.share.revoke');

    // ── §7.4 Bookmarks ──────────────────────────────────────────────────────
    Route::get('bookmarks', [BookmarkController::class, 'index'])
        ->name('bookmarks.index');

    Route::post('bookmarks', [BookmarkController::class, 'store'])
        ->name('bookmarks.store');

    Route::delete('bookmarks/{id}', [BookmarkController::class, 'destroy'])
        ->name('bookmarks.destroy');

    // ── Notebooks ────────────────────────────────────────────────────────────
    Route::get('notebooks', [NotebookController::class, 'index'])
        ->name('notebooks.index');

    Route::post('notebooks', [NotebookController::class, 'store'])
        ->name('notebooks.store');

    Route::get('notebooks/{id}', [NotebookController::class, 'show'])
        ->name('notebooks.show');

    Route::put('notebooks/{id}', [NotebookController::class, 'update'])
        ->name('notebooks.update');

    Route::delete('notebooks/{id}', [NotebookController::class, 'destroy'])
        ->name('notebooks.destroy');

    Route::post('notebooks/{id}/annotations/{annotationId}', [NotebookController::class, 'addAnnotation'])
        ->name('notebooks.annotations.attach');

    Route::delete('notebooks/{id}/annotations/{annotationId}', [NotebookController::class, 'removeAnnotation'])
        ->name('notebooks.annotations.detach');

    // ── Notes (user-authored scripture notes, PRD §5.3 / §6.5) ──────────────
    // Sync and passage routes must be before {id} to avoid route conflicts.
    Route::post('notes/sync', [NoteController::class, 'sync'])
        ->name('notes.sync');

    Route::get('notes/passage/{book}/{chapter}', [NoteController::class, 'forPassage'])
        ->name('notes.passage');

    Route::get('notes', [NoteController::class, 'index'])
        ->name('notes.index');

    Route::post('notes', [NoteController::class, 'store'])
        ->name('notes.store');

    Route::get('notes/{id}', [NoteController::class, 'show'])
        ->name('notes.show');

    Route::put('notes/{id}', [NoteController::class, 'update'])
        ->name('notes.update');

    Route::delete('notes/{id}', [NoteController::class, 'destroy'])
        ->name('notes.destroy');

    // Notebooks — add notes sub-resource
    Route::get('notebooks/{id}/notes', [NoteController::class, 'notebookNotes'])
        ->name('notebooks.notes.index');

    // ── Custom Notes (user commentary) ───────────────────────────────────────
    Route::get('custom-notes', [CustomNoteController::class, 'index'])
        ->name('custom-notes.index');

    Route::post('custom-notes', [CustomNoteController::class, 'store'])
        ->name('custom-notes.store');

    Route::put('custom-notes/{id}', [CustomNoteController::class, 'update'])
        ->name('custom-notes.update');

    Route::delete('custom-notes/{id}', [CustomNoteController::class, 'destroy'])
        ->name('custom-notes.destroy');

    // ── Study Sessions (reading history) ─────────────────────────────────────
    Route::get('study-sessions', [StudySessionController::class, 'index'])
        ->name('study-sessions.index');

    Route::post('study-sessions', [StudySessionController::class, 'store'])
        ->name('study-sessions.store');

    Route::patch('study-sessions/{id}', [StudySessionController::class, 'update'])
        ->name('study-sessions.update');
});

// ── §11.2 Public shared annotation link (no auth required) ──────────────────
Route::get('api/shared/{token}', [ShareController::class, 'show'])
    ->name('annotations.shared.show');
