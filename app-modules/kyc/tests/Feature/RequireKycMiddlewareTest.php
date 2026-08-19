<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Nucleus\Kyc\Models\KycVerification;

beforeEach(function () {
    // Set up a dummy route protected by the KYC middleware
    Route::middleware(['web', 'auth', 'kyc'])->get('/protected-route', function () {
        return 'Protected Content';
    })->name('protected');

    // Mock the KYC verify and document routes so redirects don't fail
    Route::get('/kyc', fn() => 'KYC Index')->name('kyc.index');
    Route::get('/kyc/verify', fn() => 'KYC Verify')->name('kyc.verify');
    Route::get('/kyc/documents', fn() => 'KYC Documents')->name('kyc.documents');
    Route::get('/kyc/liveness', fn() => 'KYC Liveness')->name('kyc.liveness');
});

it('allows unauthenticated users to pass through', function () {
    // Note: In a real app 'auth' middleware would catch this first, 
    // but we're testing the 'kyc' middleware in isolation
    Route::middleware(['web', 'kyc'])->get('/test-guest', function () {
        return 'Guest Content';
    });

    $response = $this->get('/test-guest');
    $response->assertOk();
    $response->assertSee('Guest Content');
});

it('redirects to verify and creates a record if no KYC record exists', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/protected-route');

    $response->assertRedirect('/kyc/verify');
    $this->assertDatabaseHas('kyc_verifications', [
        'user_id' => $user->id,
        'status' => 'pending',
    ]);
});

it('redirects to verify if KYC status is failed', function () {
    $user = User::factory()->create();
    KycVerification::create(['user_id' => $user->id, 'status' => 'failed']);

    $response = $this->actingAs($user)->get('/protected-route');

    $response->assertRedirect('/kyc/verify');
});

it('redirects to verify if KYC status is pending', function () {
    $user = User::factory()->create();
    KycVerification::create(['user_id' => $user->id, 'status' => 'pending']);

    $response = $this->actingAs($user)->get('/protected-route');

    $response->assertRedirect('/kyc/verify');
});

it('allows access to KYC routes even if not verified', function () {
    $user = User::factory()->create();
    KycVerification::create(['user_id' => $user->id, 'status' => 'pending']);

    // Explicitly apply middleware to a kyc/ route
    Route::middleware(['web', 'auth', 'kyc'])->get('/kyc/something', function () {
        return 'KYC Allowed';
    });

    $response = $this->actingAs($user)->get('/kyc/something');

    $response->assertOk();
    $response->assertSee('KYC Allowed');
});

it('redirects to verify if identity is not verified', function () {
    $user = User::factory()->create();
    KycVerification::create(['user_id' => $user->id, 'status' => 'email_verified']);

    $response = $this->actingAs($user)->get('/protected-route');

    $response->assertRedirect('/kyc/verify');
});

it('allows access to protected routes if fully verified', function () {
    $user = User::factory()->create();
    KycVerification::create([
        'user_id' => $user->id,
        'status' => 'verified',
    ]);

    $response = $this->actingAs($user)->get('/protected-route');

    $response->assertOk();
    $response->assertSee('Protected Content');
});
