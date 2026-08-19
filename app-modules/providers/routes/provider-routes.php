<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Providers Module Routes
|--------------------------------------------------------------------------
|
| Forks should add their provider-side routes here. The Filament panel
| at /provider is configured separately in
| app/Providers/Filament/ProviderPanelProvider.php.
|
*/

Route::middleware(['auth', 'verified'])->prefix('provider')->name('provider.')->group(function () {
    // Example: a registration / credential entry page.
    // Route::view('/register', 'providers::register')->name('register');
});
