<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Services\Providers\JumioKycProvider;

beforeEach(function () {
    config([
        'kyc.providers.jumio.api_token'   => 'test_jumio_token',
        'kyc.providers.jumio.api_secret'  => 'test_jumio_secret',
        'kyc.providers.jumio.base_url'    => 'https://account.amer-1.jumio.ai',
        'kyc.providers.jumio.success_url' => 'https://example.com/kyc/verify',
        'kyc.providers.jumio.error_url'   => 'https://example.com/kyc/verify',
    ]);

    $this->provider     = new JumioKycProvider();
    $this->user         = User::factory()->create();
    $this->verification = KycVerification::create([
        'user_id'  => $this->user->id,
        'provider' => 'jumio',
        'status'   => 'pending',
    ]);
});

it('has the correct name and label', function () {
    expect($this->provider->name())->toBe('jumio');
    expect($this->provider->label())->toBe('Jumio');
});

it('supports liveness and document collection', function () {
    expect($this->provider->supportsLiveness())->toBeTrue();
    expect($this->provider->supportsDocumentCollection())->toBeTrue();
});

it('throws when credentials are not configured', function () {
    config(['kyc.providers.jumio.api_token' => '']);
    $provider = new JumioKycProvider();
    $provider->initiateVerification($this->verification);
})->throws(Exception::class, 'Jumio API credentials are not configured');

it('obtains an OAuth token and creates an account on initiation', function () {
    Http::fake([
        'account.amer-1.jumio.ai/oauth2/token'    => Http::response(['access_token' => 'jwt_abc'], 200),
        'account.amer-1.jumio.ai/api/v1/accounts' => Http::response([
            'accountId' => 'acc_xyz',
            'web'       => ['href' => 'https://verify.jumio.com/start/abc', 'token' => 'tok_abc'],
        ], 200),
    ]);

    $result = $this->provider->initiateVerification($this->verification);

    expect($result['reference'])->toBe('acc_xyz');
    expect($result['url'])->toBe('https://verify.jumio.com/start/abc');
});

it('marks verified on PASSED webhook decision', function () {
    $payload = [
        'decision' => ['type' => 'PASSED'],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('verified');
});

it('marks failed on REJECTED webhook decision', function () {
    $payload = [
        'decision' => [
            'type'    => 'REJECTED',
            'details' => ['label' => 'FRAUDULENT'],
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->failure_reason)->toBe('FRAUDULENT');
});

it('marks reviewing on MANUAL_REVIEW webhook decision', function () {
    $payload = [
        'decision' => ['type' => 'MANUAL_REVIEW'],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('identity_verifying');
});

it('checks status by fetching workflow executions', function () {
    Http::fake([
        'account.amer-1.jumio.ai/oauth2/token'                          => Http::response(['access_token' => 'jwt_abc'], 200),
        'account.amer-1.jumio.ai/api/v1/accounts/acc_xyz/workflow-executions' => Http::response([
            'workflowExecutions' => [
                [
                    'id'        => 'wfe_1',
                    'startedAt' => '2026-03-18T10:00:00Z',
                    'decision'  => ['type' => 'PASSED'],
                ],
            ],
        ], 200),
    ]);

    $result = $this->provider->checkStatus('acc_xyz');

    expect($result['status'])->toBe('verified');
});

it('returns pending when no workflow executions exist', function () {
    Http::fake([
        'account.amer-1.jumio.ai/oauth2/token'                              => Http::response(['access_token' => 'jwt_abc'], 200),
        'account.amer-1.jumio.ai/api/v1/accounts/acc_xyz/workflow-executions' => Http::response([
            'workflowExecutions' => [],
        ], 200),
    ]);

    $result = $this->provider->checkStatus('acc_xyz');

    expect($result['status'])->toBe('pending');
});
