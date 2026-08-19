<?php

use App\Models\User;
use Nucleus\Kyc\Models\KycVerification;

beforeEach(function () {
    $this->user         = User::factory()->create();
    $this->verification = KycVerification::create([
        'user_id' => $this->user->id,
        'status'  => 'pending',
    ]);
});

// ─── Basic status checks ──────────────────────────────────────────────────────

it('starts as pending', function () {
    expect($this->verification->isPending())->toBeTrue();
    expect($this->verification->isVerified())->toBeFalse();
    expect($this->verification->isFailed())->toBeFalse();
    expect($this->verification->isUnderReview())->toBeFalse();
    expect($this->verification->needsMoreInfo())->toBeFalse();
});

it('detects verified status', function () {
    $this->verification->update(['status' => 'verified']);
    expect($this->verification->isVerified())->toBeTrue();
    expect($this->verification->isPending())->toBeFalse();
    expect($this->verification->isFailed())->toBeFalse();
});

it('detects failed status', function () {
    $this->verification->update(['status' => 'failed']);
    expect($this->verification->isFailed())->toBeTrue();
    expect($this->verification->isPending())->toBeFalse();
    expect($this->verification->isVerified())->toBeFalse();
});

it('detects under_review status', function () {
    $this->verification->update(['status' => 'under_review']);
    expect($this->verification->isUnderReview())->toBeTrue();
    expect($this->verification->isPending())->toBeTrue(); // isPending = not verified and not failed
});

it('detects more_info_needed status', function () {
    $this->verification->update(['status' => 'more_info_needed']);
    expect($this->verification->needsMoreInfo())->toBeTrue();
    expect($this->verification->isPending())->toBeTrue();
});

// ─── Document helpers ─────────────────────────────────────────────────────────

it('tracks document upload state correctly', function () {
    expect($this->verification->hasDocument('proof_of_address'))->toBeFalse();
    expect($this->verification->pendingDocumentTypes())->toBe(['proof_of_address', 'photo_id', 'name_change']);
    expect($this->verification->hasAllDocuments())->toBeFalse();

    $this->verification->update(['proof_of_address_path' => 'kyc/proof_of_address/poa.pdf']);
    expect($this->verification->hasDocument('proof_of_address'))->toBeTrue();
    expect($this->verification->pendingDocumentTypes())->toBe(['photo_id', 'name_change']);

    $this->verification->update([
        'photo_id_path'    => 'kyc/photo_id/id.jpg',
        'name_change_path' => 'kyc/name_change/deed.pdf',
    ]);
    expect($this->verification->hasAllDocuments())->toBeTrue();
    expect($this->verification->pendingDocumentTypes())->toBe([]);
});

it('tracks liveness check state', function () {
    expect($this->verification->hasLivenessCheck())->toBeFalse();
    expect($this->verification->needsLivenessCheck())->toBeFalse(); // no docs yet

    $this->verification->update([
        'proof_of_address_path' => 'poa.pdf',
        'photo_id_path'         => 'id.jpg',
        'name_change_path'      => 'deed.pdf',
    ]);
    expect($this->verification->needsLivenessCheck())->toBeTrue();
    expect($this->verification->isReadyForVerification())->toBeFalse();

    $this->verification->update(['liveness_video_path' => 'video.webm']);
    expect($this->verification->hasLivenessCheck())->toBeTrue();
    expect($this->verification->isReadyForVerification())->toBeTrue();
});

// ─── canBeSubmitted ───────────────────────────────────────────────────────────

it('can be submitted when all docs and liveness are ready and status allows it', function () {
    $this->verification->update([
        'proof_of_address_path' => 'poa.pdf',
        'photo_id_path'         => 'id.jpg',
        'name_change_path'      => 'deed.pdf',
        'liveness_video_path'   => 'video.webm',
    ]);

    foreach (['pending', 'documents_ready', 'more_info_needed'] as $status) {
        $this->verification->update(['status' => $status]);
        expect($this->verification->canBeSubmitted())->toBeTrue("Failed for status: {$status}");
    }
});

it('cannot be submitted when already under review', function () {
    $this->verification->update([
        'proof_of_address_path' => 'poa.pdf',
        'photo_id_path'         => 'id.jpg',
        'name_change_path'      => 'deed.pdf',
        'liveness_video_path'   => 'video.webm',
        'status'                => 'under_review',
    ]);

    expect($this->verification->canBeSubmitted())->toBeFalse();
});

it('cannot be submitted when documents are incomplete', function () {
    $this->verification->update(['proof_of_address_path' => 'poa.pdf']);
    expect($this->verification->canBeSubmitted())->toBeFalse();
});

// ─── allChecksDecided ─────────────────────────────────────────────────────────

it('reports all checks as decided when all required results are non-pending', function () {
    $this->verification->update([
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
        // name_change has no doc uploaded → optional check skipped
    ]);

    expect($this->verification->allChecksDecided())->toBeTrue();
});

it('reports not all decided when a required check is still pending', function () {
    $this->verification->update([
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'pending',
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->allChecksDecided())->toBeFalse();
});

it('includes name_change in allChecksDecided when document is uploaded', function () {
    $this->verification->update([
        'name_change_path'        => 'deed.pdf',
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'name_change_result'      => 'pending', // still pending
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->allChecksDecided())->toBeFalse();
});

// ─── checksNeedingAttention ───────────────────────────────────────────────────

it('returns checks needing attention with labels and notes', function () {
    $this->verification->update([
        'proof_of_address_result' => 'more_info',
        'proof_of_address_note'   => 'Document is too old',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'more_info',
        'liveness_note'           => 'Video is too dark',
    ]);

    $attention = $this->verification->checksNeedingAttention();

    expect($attention)->toHaveKeys(['proof_of_address', 'liveness']);
    expect($attention['proof_of_address']['note'])->toBe('Document is too old');
    expect($attention['liveness']['note'])->toBe('Video is too dark');
    expect($attention)->not->toHaveKey('photo_id');
});

it('excludes optional name_change from attention when no document uploaded', function () {
    $this->verification->update([
        'name_change_result' => 'more_info',
        'name_change_note'   => 'Could not read the name',
    ]);

    $attention = $this->verification->checksNeedingAttention();
    expect($attention)->not->toHaveKey('name_change');
});

// ─── deriveOverallResult ──────────────────────────────────────────────────────

it('derives verified when all applicable checks pass', function () {
    $this->verification->update([
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
        // name_change skipped (no doc)
    ]);

    expect($this->verification->deriveOverallResult())->toBe('verified');
});

it('derives failed when any check fails', function () {
    $this->verification->update([
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'failed',
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->deriveOverallResult())->toBe('failed');
});

it('derives more_info_needed when any check needs more info with no failures', function () {
    $this->verification->update([
        'proof_of_address_result' => 'more_info',
        'photo_id_result'         => 'passed',
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->deriveOverallResult())->toBe('more_info_needed');
});

it('prioritises failed over more_info_needed', function () {
    $this->verification->update([
        'proof_of_address_result' => 'failed',
        'photo_id_result'         => 'more_info',
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->deriveOverallResult())->toBe('failed');
});

it('derives under_review when some checks are still pending', function () {
    $this->verification->update([
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'pending',
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->deriveOverallResult())->toBe('under_review');
});

it('includes name_change in derivation when document was uploaded', function () {
    $this->verification->update([
        'name_change_path'        => 'deed.pdf',
        'proof_of_address_result' => 'passed',
        'photo_id_result'         => 'passed',
        'name_change_result'      => 'failed',
        'liveness_result'         => 'passed',
    ]);

    expect($this->verification->deriveOverallResult())->toBe('failed');
});

// ─── fail() helper ────────────────────────────────────────────────────────────

it('can force a fail state', function () {
    $this->verification->fail('Test reason');

    expect($this->verification->status)->toBe('failed');
    expect($this->verification->failure_reason)->toBe('Test reason');
    expect($this->verification->failed_at)->not->toBeNull();
});
