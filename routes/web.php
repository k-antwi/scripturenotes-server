<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\LoginRedirectController;
use Wave\Facades\Wave;

// Post-login role-based redirect
Route::get('/auth/redirect', LoginRedirectController::class)
    ->middleware('auth')
    ->name('auth.redirect');

// Wave routes
Wave::routes();
