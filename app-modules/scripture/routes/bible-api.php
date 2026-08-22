<?php

use Illuminate\Support\Facades\Route;
use Nucleus\Scripture\Http\Controllers\BibleController;

/*
|--------------------------------------------------------------------------
| Bible API Routes — unified multi-provider gateway
|--------------------------------------------------------------------------
| All routes use throttle:120,1 (120 requests per minute per user).
| Auth is via Sanctum. The Vue/Capacitor frontend must never call
| external Bible APIs directly — everything goes through these endpoints.
*/

Route::prefix('api/bible')
    ->middleware(['auth:api', 'throttle:120,1'])
    ->group(function () {

        // GET /api/bible/passage?ref=John+3:16&version=NIV
        Route::get('/passage', [BibleController::class, 'passage'])
            ->name('bible.passage');

        // GET /api/bible/chapter?book=JHN&chapter=3&version=NIV
        Route::get('/chapter', [BibleController::class, 'chapter'])
            ->name('bible.chapter');

        // GET /api/bible/search?q=faith+hope+love&version=ESV&type=keyword|semantic
        Route::get('/search', [BibleController::class, 'search'])
            ->name('bible.search');

        // GET /api/bible/audio?ref=Psalm+23&version=KJV
        Route::get('/audio', [BibleController::class, 'audio'])
            ->name('bible.audio');

        // GET /api/bible/verse-of-day
        Route::get('/verse-of-day', [BibleController::class, 'verseOfDay'])
            ->name('bible.verse-of-day');

        // GET /api/bible/dictionary?word=grace
        Route::get('/dictionary', [BibleController::class, 'dictionary'])
            ->name('bible.dictionary');

        // GET /api/bible/versions?language=en
        Route::get('/versions', [BibleController::class, 'versions'])
            ->name('bible.versions');
    });
