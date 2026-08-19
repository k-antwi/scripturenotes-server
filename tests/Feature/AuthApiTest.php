<?php

use App\Models\User;

it('validates against duplicate emails during API registration', function () {
    $existingUser = User::factory()->create([
        'email' => 'duplicate@example.com',
    ]);

    $response = $this->postJson('/api/register', [
        'name' => 'New User',
        'email' => 'duplicate@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('allows registration with a unique email', function () {
    $response = $this->postJson('/api/register', [
        'name' => 'Unique User',
        'email' => 'unique_user_' . time() . '@example.com',
        'password' => 'secret123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in']);
});

it('auto-generates username if omitted during API registration', function () {
    $email = 'autouser_' . time() . '@example.com';
    
    $response = $this->postJson('/api/register', [
        'name' => 'Auto User',
        'email' => $email,
        'password' => 'secret123',
    ]);

    $response->assertStatus(200);

    $user = User::where('email', $email)->first();
    expect($user)->not->toBeNull();
    expect($user->username)->toBe('auto-user');
});
