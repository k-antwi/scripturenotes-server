<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/brain', function () {
        return view('brain::pages.brain', [
            'title' => config('brain.assistant_title', 'Assistant'),
        ]);
    })->name('brain.chat');
});
