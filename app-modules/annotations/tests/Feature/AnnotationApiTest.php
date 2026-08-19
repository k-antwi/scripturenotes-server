<?php

use App\Models\User;
use Nucleus\Annotations\Models\Annotation;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('GET /api/annotations', function () {
    it('requires authentication', function () {
        $this->getJson('/api/annotations')->assertUnauthorized();
    });

    it('lists only the authenticated user annotations', function () {
        $other = User::factory()->create();
        Annotation::factory()->create(['user_id' => $other->id, 'book' => 'GEN', 'chapter' => 1, 'type' => 'highlight']);
        Annotation::factory()->create(['user_id' => $this->user->id, 'book' => 'PRO', 'chapter' => 19, 'type' => 'pen']);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/annotations')
            ->assertOk()
            ->assertJsonCount(1);
    });

    it('filters by book and chapter', function () {
        Annotation::factory()->count(3)->create(['user_id' => $this->user->id, 'book' => 'PRO', 'chapter' => 19, 'type' => 'highlight']);
        Annotation::factory()->count(2)->create(['user_id' => $this->user->id, 'book' => 'GEN', 'chapter' => 1, 'type' => 'pen']);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/annotations?book=PRO&chapter=19')
            ->assertOk()
            ->assertJsonCount(3);
    });

    it('excludes soft-deleted annotations', function () {
        Annotation::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'PRO',
            'chapter' => 1,
            'type' => 'highlight',
            'deleted_at' => now(),
        ]);

        $this->actingAs($this->user, 'api')
            ->getJson('/api/annotations')
            ->assertOk()
            ->assertJsonCount(0);
    });
});

describe('POST /api/annotations', function () {
    it('creates a highlight annotation', function () {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/annotations', [
                'book' => 'PRO',
                'chapter' => 19,
                'verse' => 1,
                'type' => 'highlight',
                'colour' => '#FFD700',
                'data' => ['charStart' => 0, 'charEnd' => 10],
            ])
            ->assertCreated()
            ->assertJsonPath('type', 'highlight')
            ->assertJsonPath('book', 'PRO');
    });

    it('normalises book to uppercase', function () {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/annotations', [
                'book' => 'pro',
                'chapter' => 1,
                'type' => 'pen',
            ])
            ->assertCreated()
            ->assertJsonPath('book', 'PRO');
    });

    it('rejects invalid annotation type', function () {
        $this->actingAs($this->user, 'api')
            ->postJson('/api/annotations', [
                'book' => 'PRO',
                'chapter' => 1,
                'type' => 'invalid_type',
            ])
            ->assertUnprocessable();
    });
});

describe('PUT /api/annotations/{id}', function () {
    it('updates annotation colour', function () {
        $annotation = Annotation::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'PRO',
            'chapter' => 1,
            'type' => 'highlight',
            'colour' => '#FF0000',
        ]);

        $this->actingAs($this->user, 'api')
            ->putJson("/api/annotations/{$annotation->id}", ['colour' => '#00FF00'])
            ->assertOk()
            ->assertJsonPath('colour', '#00FF00');
    });

    it('cannot update another user annotation', function () {
        $other = User::factory()->create();
        $annotation = Annotation::factory()->create([
            'user_id' => $other->id,
            'book' => 'PRO',
            'chapter' => 1,
            'type' => 'pen',
        ]);

        $this->actingAs($this->user, 'api')
            ->putJson("/api/annotations/{$annotation->id}", ['colour' => '#00FF00'])
            ->assertNotFound();
    });
});

describe('DELETE /api/annotations/{id}', function () {
    it('soft-deletes the annotation', function () {
        $annotation = Annotation::factory()->create([
            'user_id' => $this->user->id,
            'book' => 'PRO',
            'chapter' => 1,
            'type' => 'highlight',
        ]);

        $this->actingAs($this->user, 'api')
            ->deleteJson("/api/annotations/{$annotation->id}")
            ->assertOk()
            ->assertJsonPath('deleted', true);

        expect(Annotation::find($annotation->id)->deleted_at)->not->toBeNull();
    });
});

describe('POST /api/annotations/sync', function () {
    it('batch upserts annotations and returns localId/remoteId pairs', function () {
        $mutations = [
            [
                'localId' => 'local-1',
                'remoteId' => null,
                'action' => 'create',
                'book' => 'JHN',
                'chapter' => 3,
                'verse' => 16,
                'type' => 'highlight',
                'colour' => '#FFD700',
                'data' => [],
            ],
        ];

        $response = $this->actingAs($this->user, 'api')
            ->postJson('/api/annotations/sync', ['mutations' => $mutations])
            ->assertOk();

        $results = $response->json('results');
        expect($results)->toHaveCount(1);
        expect($results[0]['localId'])->toBe('local-1');
        expect($results[0]['remoteId'])->toBeInt();
    });
});
