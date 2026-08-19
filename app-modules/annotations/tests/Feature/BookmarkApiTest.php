<?php

use App\Models\User;
use Nucleus\Annotations\Models\Bookmark;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('lists bookmarks for authenticated user', function () {
    Bookmark::factory()->count(3)->create(['user_id' => $this->user->id]);
    Bookmark::factory()->create(['user_id' => User::factory()->create()->id]);

    $this->actingAs($this->user, 'api')
        ->getJson('/api/bookmarks')
        ->assertOk()
        ->assertJsonCount(3);
});

it('creates a bookmark', function () {
    $this->actingAs($this->user, 'api')
        ->postJson('/api/bookmarks', [
            'book' => 'PSA',
            'chapter' => 23,
            'verse' => 1,
            'label' => 'My favourite verse',
        ])
        ->assertCreated()
        ->assertJsonPath('book', 'PSA')
        ->assertJsonPath('label', 'My favourite verse');
});

it('is idempotent — does not duplicate the same bookmark', function () {
    $this->actingAs($this->user, 'api')
        ->postJson('/api/bookmarks', ['book' => 'PSA', 'chapter' => 23, 'verse' => 1])
        ->assertCreated();

    $this->actingAs($this->user, 'api')
        ->postJson('/api/bookmarks', ['book' => 'PSA', 'chapter' => 23, 'verse' => 1])
        ->assertCreated();

    expect(Bookmark::where('user_id', $this->user->id)->count())->toBe(1);
});

it('deletes a bookmark', function () {
    $bookmark = Bookmark::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'api')
        ->deleteJson("/api/bookmarks/{$bookmark->id}")
        ->assertOk()
        ->assertJsonPath('deleted', true);

    expect(Bookmark::find($bookmark->id))->toBeNull();
});
