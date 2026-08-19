<?php

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Nucleus\Kyc\Events\KycFailed;
use Nucleus\Kyc\Events\KycMoreInfoRequested;
use Nucleus\Kyc\Events\KycVerified;
use Nucleus\Kyc\Filament\Resources\KycVerificationResource;
use Nucleus\Kyc\Models\KycVerification;

/**
 * Unit-level tests for KycVerificationResource::saveDecision().
 * These test the business logic without needing a Filament panel.
 */
beforeEach(function () {
    $this->reviewer = User::factory()->create();
    $this->applicant = User::factory()->create();

    $this->verification = KycVerification::create([
        'user_id'               => $this->applicant->id,
        'status'                => 'under_review',
        'proof_of_address_path' => 'kyc/proof_of_address/poa.pdf',
        'photo_id_path'         => 'kyc/photo_id/id.jpg',
        'name_change_path'      => null,
        'liveness_video_path'   => 'kyc/liveness/video.webm',
        'submitted_at'          => now()->subHour(),
    ]);

    $this->actingAs($this->reviewer);
});

// ─── saveDecision: verified outcome ──────────────────────────────────────────

it('marks verification as verified when all checks pass', function () {
    Event::fake([KycVerified::class]);

    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
        'overall_review_note'     => null,
        'status'                  => null,
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('verified');
    expect($fresh->identity_verified_at)->not->toBeNull();
    expect($fresh->review_completed_at)->not->toBeNull();

    Event::assertDispatched(KycVerified::class, fn ($e) =>
        $e->verification->id === $this->verification->id
    );
    Event::assertNotDispatched(KycFailed::class);
    Event::assertNotDispatched(KycMoreInfoRequested::class);
});

// ─── saveDecision: failed outcome ────────────────────────────────────────────

it('marks verification as failed when any check fails', function () {
    Event::fake([KycFailed::class]);

    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'failed',
        'liveness_result'         => 'passed',
        'photo_id_note'           => 'ID appears to be altered',
        'overall_review_note'     => 'We could not verify your identity.',
        'status'                  => null,
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->failed_at)->not->toBeNull();
    expect($fresh->photo_id_note)->toBe('ID appears to be altered');
    expect($fresh->overall_review_note)->toBe('We could not verify your identity.');

    Event::assertDispatched(KycFailed::class);
    Event::assertNotDispatched(KycVerified::class);
});

// ─── saveDecision: more_info_needed outcome ───────────────────────────────────

it('marks more_info_needed and clears flagged document paths', function () {
    Event::fake([KycMoreInfoRequested::class]);

    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'more_info',
        'proof_of_address_note'   => 'Document is out of date',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
        'overall_review_note'     => null,
        'status'                  => null,
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('more_info_needed');

    // Flagged document path should be cleared so the user can re-upload
    expect($fresh->proof_of_address_path)->toBeNull();
    expect($fresh->proof_of_address_uploaded_at)->toBeNull();
    // Proof of address result reset to pending for re-upload
    expect($fresh->proof_of_address_result)->toBe('pending');

    // Non-flagged document paths must remain intact
    expect($fresh->photo_id_path)->not->toBeNull();

    Event::assertDispatched(KycMoreInfoRequested::class);
    Event::assertNotDispatched(KycVerified::class);
    Event::assertNotDispatched(KycFailed::class);
});

// ─── saveDecision: manual override ───────────────────────────────────────────

it('respects a manual status override over the derived result', function () {
    Event::fake([KycFailed::class]);

    // All checks pass → would normally derive 'verified', but override to 'failed'
    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
        'overall_review_note'     => 'Manually overridden due to compliance policy.',
        'status'                  => 'failed',
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');

    Event::assertDispatched(KycFailed::class);
    Event::assertNotDispatched(KycVerified::class);
});

// ─── saveDecision: name_change included when uploaded ────────────────────────

it('includes name_change in derivation when document is uploaded', function () {
    Event::fake([KycMoreInfoRequested::class]);

    $this->verification->update(['name_change_path' => 'kyc/name_change/deed.pdf']);

    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'name_change_result'      => 'more_info',
        'name_change_note'        => 'Names do not match',
        'liveness_result'         => 'passed',
        'overall_review_note'     => null,
        'status'                  => null,
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('more_info_needed');

    // Name change doc cleared for re-upload
    expect($fresh->name_change_path)->toBeNull();

    Event::assertDispatched(KycMoreInfoRequested::class);
});

// ─── saveDecision: reviewer metadata ─────────────────────────────────────────

it('records reviewer and review_completed_at timestamps', function () {
    Event::fake();

    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
        'overall_review_note'     => null,
        'status'                  => null,
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->reviewed_by)->toBe($this->reviewer->id);
    expect($fresh->review_completed_at)->not->toBeNull();
});

// ─── deriveOverallResult used correctly ───────────────────────────────────────

it('uses deriveOverallResult when no manual override is given', function () {
    Event::fake();

    // Simulate multiple checks: failed takes priority
    KycVerificationResource::saveDecision($this->verification, [
        'proof_of_address_result' => 'failed',
        'photo_id_result'         => 'more_info',
        'liveness_result'         => 'passed',
        'overall_review_note'     => null,
        'status'                  => null, // no override
    ]);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed'); // derived, not overridden
});
