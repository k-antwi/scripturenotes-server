<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Services\Providers\StripeKycProvider;

beforeEach(function () {
    config([
        'kyc.providers.stripe.secret_key'     => 'sk_test_fake',
        'kyc.providers.stripe.webhook_secret'  => 'whsec_fake',
        'kyc.providers.stripe.return_url'      => 'https://example.com/kyc/verify',
    ]);

    $this->provider     = new StripeKycProvider();
    $this->user         = User::factory()->create();
    $this->verification = KycVerification::create([
        'user_id'  => $this->user->id,
        'provider' => 'stripe',
        'status'   => 'pending',
    ]);
});

it('has the correct name and label', function () {
    expect($this->provider->name())->toBe('stripe');
    expect($this->provider->label())->toBe('Stripe Identity');
});

it('supports liveness and document collection', function () {
    expect($this->provider->supportsLiveness())->toBeTrue();
    expect($this->provider->supportsDocumentCollection())->toBeTrue();
});

it('throws when secret key is not configured', function () {
    config(['kyc.providers.stripe.secret_key' => '']);
    $provider = new StripeKycProvider();
    $provider->initiateVerification($this->verification);
})->throws(Exception::class, 'Stripe Identity secret key is not configured');

it('creates a verification session via Stripe API', function () {
    Http::fake([
        'api.stripe.com/v1/identity/verification_sessions' => Http::response([
            'id'            => 'vs_test_123',
            'url'           => 'https://verify.stripe.com/start/test',
            'client_secret' => 'vs_test_123_secret',
            'status'        => 'requires_input',
        ], 200),
    ]);

    $result = $this->provider->initiateVerification($this->verification);

    expect($result['reference'])->toBe('vs_test_123');
    expect($result['url'])->toBe('https://verify.stripe.com/start/test');
    expect($result['token'])->toBe('vs_test_123_secret');
});

it('marks verification as verified on webhook verified event', function () {
    $payload = [
        'type' => 'identity.verification_session.verified',
        'data' => ['object' => ['id' => 'vs_test_123', 'status' => 'verified']],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('verified');
    expect($this->verification->fresh()->identity_verified_at)->not->toBeNull();
});

it('marks verification as failed on webhook requires_input event', function () {
    $payload = [
        'type' => 'identity.verification_session.requires_input',
        'data' => [
            'object' => [
                'id'         => 'vs_test_123',
                'status'     => 'requires_input',
                'last_error' => ['reason' => 'document_expired'],
            ],
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->failure_reason)->toBe('document_expired');
});

it('marks verification as reviewing on webhook processing event', function () {
    $payload = [
        'type' => 'identity.verification_session.processing',
        'data' => ['object' => ['id' => 'vs_test_123', 'status' => 'processing']],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('identity_verifying');
});

it('checks status via Stripe API', function () {
    Http::fake([
        'api.stripe.com/v1/identity/verification_sessions/vs_test_123' => Http::response([
            'id'     => 'vs_test_123',
            'status' => 'verified',
        ], 200),
    ]);

    $result = $this->provider->checkStatus('vs_test_123');

    expect($result['status'])->toBe('verified');
    expect($result['raw']['id'])->toBe('vs_test_123');
});

it('maps stripe statuses to normalised values', function () {
    Http::fake([
        'api.stripe.com/v1/identity/verification_sessions/*' => Http::sequence()
            ->push(['id' => 'x', 'status' => 'verified'], 200)
            ->push(['id' => 'x', 'status' => 'requires_input'], 200)
            ->push(['id' => 'x', 'status' => 'processing'], 200)
            ->push(['id' => 'x', 'status' => 'canceled'], 200),
    ]);

    expect($this->provider->checkStatus('x')['status'])->toBe('verified');
    expect($this->provider->checkStatus('x')['status'])->toBe('failed');
    expect($this->provider->checkStatus('x')['status'])->toBe('reviewing');
    expect($this->provider->checkStatus('x')['status'])->toBe('pending');
});
