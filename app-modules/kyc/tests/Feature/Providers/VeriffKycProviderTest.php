<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Services\Providers\VeriffKycProvider;

beforeEach(function () {
    config([
        'kyc.providers.veriff.api_key'    => 'test_veriff_key',
        'kyc.providers.veriff.api_secret' => 'test_veriff_secret',
        'kyc.providers.veriff.base_url'   => 'https://stationapi.veriff.com',
    ]);

    $this->provider     = new VeriffKycProvider();
    $this->user         = User::factory()->create(['first_name' => 'John', 'last_name' => 'Smith']);
    $this->verification = KycVerification::create([
        'user_id'  => $this->user->id,
        'provider' => 'veriff',
        'status'   => 'pending',
    ]);
});

it('has the correct name and label', function () {
    expect($this->provider->name())->toBe('veriff');
    expect($this->provider->label())->toBe('Veriff');
});

it('supports liveness and document collection', function () {
    expect($this->provider->supportsLiveness())->toBeTrue();
    expect($this->provider->supportsDocumentCollection())->toBeTrue();
});

it('throws when api key is not configured', function () {
    config(['kyc.providers.veriff.api_key' => '']);
    $provider = new VeriffKycProvider();
    $provider->initiateVerification($this->verification);
})->throws(Exception::class, 'Veriff API key is not configured');

it('creates a session and returns hosted URL on initiation', function () {
    Http::fake([
        'stationapi.veriff.com/v1/sessions' => Http::response([
            'status' => 'success',
            'verification' => [
                'id'  => 'sess_veriff_123',
                'url' => 'https://magic.veriff.me/v/abc',
            ],
        ], 200),
    ]);

    $result = $this->provider->initiateVerification($this->verification);

    expect($result['reference'])->toBe('sess_veriff_123');
    expect($result['url'])->toBe('https://magic.veriff.me/v/abc');
    expect($result['token'])->toBeNull();
});

it('marks verified on webhook with code 9001', function () {
    $payload = [
        'verification' => ['code' => 9001, 'status' => 'approved'],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('verified');
});

it('marks verified on webhook with approved status string', function () {
    $payload = [
        'verification' => ['code' => 0, 'status' => 'approved'],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('verified');
});

it('marks failed on webhook with code 9102', function () {
    $payload = [
        'verification' => [
            'code'   => 9102,
            'status' => 'declined',
            'reason' => 'Document damaged',
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->failure_reason)->toBe('Document damaged');
});

it('marks failed on webhook with code 9103 (resubmission_requested)', function () {
    $payload = [
        'verification' => ['code' => 9103, 'status' => 'resubmission_requested'],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('failed');
});

it('marks reviewing on webhook with code 9104', function () {
    $payload = [
        'verification' => ['code' => 9104, 'status' => 'review'],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('identity_verifying');
});

it('checks status via Veriff decision API', function () {
    Http::fake([
        'stationapi.veriff.com/v1/sessions/sess_123/decision' => Http::response([
            'status' => 'success',
            'verification' => [
                'id'     => 'sess_123',
                'code'   => 9001,
                'status' => 'approved',
            ],
        ], 200),
    ]);

    $result = $this->provider->checkStatus('sess_123');

    expect($result['status'])->toBe('verified');
});

it('returns pending when Veriff returns 404 for decision', function () {
    Http::fake([
        'stationapi.veriff.com/v1/sessions/not_found/decision' => Http::response([], 404),
    ]);

    $result = $this->provider->checkStatus('not_found');

    expect($result['status'])->toBe('pending');
});
