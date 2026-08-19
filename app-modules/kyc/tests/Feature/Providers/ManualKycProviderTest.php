<?php

use App\Models\User;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Services\Providers\ManualKycProvider;

beforeEach(function () {
    $this->provider     = new ManualKycProvider();
    $this->user         = User::factory()->create();
    $this->verification = KycVerification::create([
        'user_id'  => $this->user->id,
        'provider' => 'manual',
        'status'   => 'pending',
    ]);
});

it('has the correct name and label', function () {
    expect($this->provider->name())->toBe('manual');
    expect($this->provider->label())->toBe('Manual Review');
});

it('does not support native liveness or document collection', function () {
    expect($this->provider->supportsLiveness())->toBeFalse();
    expect($this->provider->supportsDocumentCollection())->toBeFalse();
});

it('returns the verification id as the reference on initiation', function () {
    $result = $this->provider->initiateVerification($this->verification);

    expect($result['reference'])->toBe((string) $this->verification->id);
    expect($result['url'])->toBeNull();
    expect($result['token'])->toBeNull();
});

it('returns the correct normalised status', function () {
    foreach ([
        'pending'            => 'pending',
        'email_verified'     => 'pending',
        'identity_verifying' => 'reviewing',
        'identity_slow'      => 'reviewing',
        'verified'           => 'verified',
        'failed'             => 'failed',
        'failed_submitted'   => 'failed',
    ] as $rawStatus => $expectedNormalised) {
        $this->verification->update(['status' => $rawStatus]);
        $result = $this->provider->checkStatus((string) $this->verification->id);
        expect($result['status'])->toBe($expectedNormalised, "Failed for status: {$rawStatus}");
    }
});

it('is a no-op when handleWebhook is called', function () {
    $this->provider->handleWebhook(['foo' => 'bar'], $this->verification);
    expect($this->verification->fresh()->status)->toBe('pending');
});
