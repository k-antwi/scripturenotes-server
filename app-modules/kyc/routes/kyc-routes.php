<?php

use Illuminate\Support\Facades\Route;
use Nucleus\Kyc\Http\Controllers\KycFileController;

Route::middleware(['web', 'auth', 'kyc.reviewer'])
    ->get('/kyc/files/{path}', KycFileController::class)
    ->where('path', '.*')
    ->name('kyc.files');
