<?php

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Nucleus\Kyc\Events\KycSubmittedForReview;
use Nucleus\Kyc\Livewire\KycStatus;
use Nucleus\Kyc\Models\KycVerification;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('creates a pending verification on mount', function () {
    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->assertSet('verification.status', 'pending');

    $this->assertDatabaseHas('kyc_verifications', [
        'user_id' => $this->user->id,
        'status'  => 'pending',
    ]);
});

it('redirects to dashboard when verification is already verified on poll', function () {
    KycVerification::create([
        'user_id' => $this->user->id,
        'status'  => 'verified',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->call('poll')
        ->assertRedirect('/dashboard');
});

it('does not advance state machine when polling', function () {
    $verification = KycVerification::create([
        'user_id' => $this->user->id,
        'status'  => 'under_review',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->call('poll')
        ->assertSet('verification.status', 'under_review');

    expect($verification->fresh()->status)->toBe('under_review');
});

it('submitForReview sets status to under_review and fires event', function () {
    Event::fake([KycSubmittedForReview::class]);

    $verification = KycVerification::create([
        'user_id'               => $this->user->id,
        'status'                => 'pending',
        'proof_of_address_path' => 'poa.pdf',
        'photo_id_path'         => 'id.jpg',
        'name_change_path'      => 'deed.pdf',
        'liveness_video_path'   => 'video.webm',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->call('submitForReview');

    $fresh = $verification->fresh();
    expect($fresh->status)->toBe('under_review');
    expect($fresh->submitted_at)->not->toBeNull();

    Event::assertDispatched(KycSubmittedForReview::class, fn ($e) =>
        $e->verification->id === $verification->id
    );
});

it('submitForReview does nothing when documents are not ready', function () {
    Event::fake([KycSubmittedForReview::class]);

    $verification = KycVerification::create([
        'user_id' => $this->user->id,
        'status'  => 'pending',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->call('submitForReview');

    expect($verification->fresh()->status)->toBe('pending');
    Event::assertNotDispatched(KycSubmittedForReview::class);
});

it('submitForReview does nothing when already under_review', function () {
    Event::fake([KycSubmittedForReview::class]);

    $verification = KycVerification::create([
        'user_id'               => $this->user->id,
        'status'                => 'under_review',
        'proof_of_address_path' => 'poa.pdf',
        'photo_id_path'         => 'id.jpg',
        'name_change_path'      => 'deed.pdf',
        'liveness_video_path'   => 'video.webm',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->call('submitForReview');

    expect($verification->fresh()->status)->toBe('under_review');
    Event::assertNotDispatched(KycSubmittedForReview::class);
});

it('shows the submit button when all documents and liveness are ready', function () {
    KycVerification::create([
        'user_id'               => $this->user->id,
        'status'                => 'pending',
        'proof_of_address_path' => 'poa.pdf',
        'photo_id_path'         => 'id.jpg',
        'name_change_path'      => 'deed.pdf',
        'liveness_video_path'   => 'video.webm',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->assertSee('Submit for review');
});

it('shows waiting state when under review', function () {
    KycVerification::create([
        'user_id' => $this->user->id,
        'status'  => 'under_review',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->assertSee('under review');
});

it('shows more_info_needed checks with reviewer notes', function () {
    KycVerification::create([
        'user_id'                 => $this->user->id,
        'status'                  => 'more_info_needed',
        'proof_of_address_result' => 'more_info',
        'proof_of_address_note'   => 'Document is too old',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
    ]);

    Livewire::actingAs($this->user)
        ->test(KycStatus::class)
        ->assertSee('Additional information required')
        ->assertSee('Document is too old');
});
