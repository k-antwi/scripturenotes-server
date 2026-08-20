<?php

use App\Models\User;
use Nucleus\Annotations\Models\Annotation;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

it('generates a share token for the authenticated owner', function () {
    $annotation = Annotation::factory()->create(['user_id' => $this->user->id]);

    $response = $this->postJson("/api/annotations/{$annotation->id}/share");

    $response->assertOk()
             ->assertJsonStructure(['share_token', 'share_url', 'annotation']);

    expect($annotation->fresh()->is_shared)->toBeTrue();
    expect($annotation->fresh()->share_token)->not->toBeNull();
});

it('returns the same share token on repeated share calls', function () {
    $annotation = Annotation::factory()->create(['user_id' => $this->user->id]);

    $first  = $this->postJson("/api/annotations/{$annotation->id}/share")->json('share_token');
    $second = $this->postJson("/api/annotations/{$annotation->id}/share")->json('share_token');

    expect($first)->toBe($second);
});

it('forbids sharing another user\'s annotation', function () {
    $other = User::factory()->create();
    $annotation = Annotation::factory()->create(['user_id' => $other->id]);

    $this->postJson("/api/annotations/{$annotation->id}/share")
         ->assertNotFound();
});

it('public shared link returns the annotation', function () {
    $annotation = Annotation::factory()->create([
        'user_id'     => $this->user->id,
        'is_shared'   => true,
        'share_token' => 'test-token-abc',
    ]);

    // Guest request — no auth header
    $this->withoutMiddleware()
         ->getJson('/api/shared/test-token-abc')
         ->assertOk()
         ->assertJsonFragment(['id' => $annotation->id]);
});

it('public link returns 404 when sharing is revoked', function () {
    $annotation = Annotation::factory()->create([
        'user_id'     => $this->user->id,
        'is_shared'   => false,
        'share_token' => 'revoked-token',
    ]);

    $this->withoutMiddleware()
         ->getJson('/api/shared/revoked-token')
         ->assertNotFound();
});

it('revokes sharing', function () {
    $annotation = Annotation::factory()->create([
        'user_id'   => $this->user->id,
        'is_shared' => true,
        'share_token' => 'some-token',
    ]);

    $this->deleteJson("/api/annotations/{$annotation->id}/share")
         ->assertOk()
         ->assertJson(['revoked' => true]);

    expect($annotation->fresh()->is_shared)->toBeFalse();
});
