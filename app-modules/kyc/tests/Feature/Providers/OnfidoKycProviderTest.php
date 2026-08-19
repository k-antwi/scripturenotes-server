<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;
use Nucleus\Kyc\Services\Providers\OnfidoKycProvider;

beforeEach(function () {
    config([
        'kyc.providers.onfido.api_token'     => 'test_onfido_token',
        'kyc.providers.onfido.webhook_token' => 'test_webhook_token',
        'kyc.providers.onfido.region'        => 'eu',
    ]);

    $this->provider     = new OnfidoKycProvider();
    $this->user         = User::factory()->create(['first_name' => 'Jane', 'last_name' => 'Doe']);
    $this->verification = KycVerification::create([
        'user_id'  => $this->user->id,
        'provider' => 'onfido',
        'status'   => 'pending',
    ]);
});

it('has the correct name and label', function () {
    expect($this->provider->name())->toBe('onfido');
    expect($this->provider->label())->toBe('Onfido');
});

it('supports liveness and document collection', function () {
    expect($this->provider->supportsLiveness())->toBeTrue();
    expect($this->provider->supportsDocumentCollection())->toBeTrue();
});

it('throws when api token is not configured', function () {
    config(['kyc.providers.onfido.api_token' => '']);
    $provider = new OnfidoKycProvider();
    $provider->initiateVerification($this->verification);
})->throws(Exception::class, 'Onfido API token is not configured');

it('creates an applicant and returns SDK token on initiation', function () {
    Http::fake([
        'api.eu.onfido.com/v3.6/applicants'  => Http::response(['id' => 'app_123'], 201),
        'api.eu.onfido.com/v3.6/sdk_token'   => Http::response(['token' => 'sdk_token_abc'], 200),
    ]);

    $result = $this->provider->initiateVerification($this->verification);

    expect($result['reference'])->toBe('app_123');
    expect($result['token'])->toBe('sdk_token_abc');
    expect($result['url'])->toBeNull();
});

it('marks verified on check.completed clear webhook', function () {
    $payload = [
        'payload' => [
            'action' => 'check.completed',
            'object' => ['result' => 'clear'],
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('verified');
});

it('marks failed on check.completed consider webhook', function () {
    $payload = [
        'payload' => [
            'action' => 'check.completed',
            'object' => ['result' => 'consider'],
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->failure_reason)->toContain('consider');
});

it('marks verified on workflow_run.completed approved webhook', function () {
    $payload = [
        'payload' => [
            'action' => 'workflow_run.completed',
            'object' => ['status' => 'approved'],
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    expect($this->verification->fresh()->status)->toBe('verified');
});

it('marks failed on workflow_run.completed rejected webhook', function () {
    $payload = [
        'payload' => [
            'action' => 'workflow_run.completed',
            'object' => [
                'status'  => 'declined',
                'reasons' => ['Document not accepted'],
            ],
        ],
    ];

    $this->provider->handleWebhook($payload, $this->verification);

    $fresh = $this->verification->fresh();
    expect($fresh->status)->toBe('failed');
    expect($fresh->failure_reason)->toBe('Document not accepted');
});

it('checks status by listing checks for the applicant', function () {
    Http::fake([
        'api.eu.onfido.com/v3.6/checks*' => Http::response([
            'checks' => [
                ['id' => 'chk_1', 'result' => 'clear', 'created_at' => '2026-03-18T10:00:00Z'],
            ],
        ], 200),
    ]);

    $result = $this->provider->checkStatus('app_123');

    expect($result['status'])->toBe('verified');
    expect($result['raw']['id'])->toBe('chk_1');
});

it('maps onfido statuses to normalised values', function () {
    $provider = $this->provider;

    // Use reflection to test the protected method directly
    $reflection = new ReflectionClass($provider);
    $method     = $reflection->getMethod('mapStatus');
    $method->setAccessible(true);

    expect($method->invoke($provider, 'clear'))->toBe('verified');
    expect($method->invoke($provider, 'approved'))->toBe('verified');
    expect($method->invoke($provider, 'consider'))->toBe('failed');
    expect($method->invoke($provider, 'rejected'))->toBe('failed');
    expect($method->invoke($provider, 'in_progress'))->toBe('reviewing');
    expect($method->invoke($provider, 'awaiting_applicant'))->toBe('reviewing');
    expect($method->invoke($provider, 'unknown'))->toBe('pending');
});
