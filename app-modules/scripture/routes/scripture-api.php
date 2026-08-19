<?php

use Illuminate\Support\Facades\Route;
use Nucleus\Scripture\Http\Controllers\PassageController;

/*
|--------------------------------------------------------------------------
| Scripture API Routes (PRD §7.3 — Passages Cache Proxy)
|--------------------------------------------------------------------------
| These routes require no authentication: reading scripture is always
| available (guests annotate locally in Dexie, sync on login).
*/

Route::prefix('api/passages')->group(function () {
    Route::get('/{book}/{chapter}', [PassageController::class, 'show'])
        ->name('passages.show')
        ->where('book', '[A-Za-z0-9]+')
        ->where('chapter', '[0-9]+');

    Route::get('/{book}/{chapter}/notes', [PassageController::class, 'notes'])
        ->name('passages.notes')
        ->where('book', '[A-Za-z0-9]+')
        ->where('chapter', '[0-9]+');
});
