<?php

namespace Nucleus\Kyc\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;

/**
 * Jumio KYC provider.
 *
 * Docs: https://developers.jumio.com/
 *
 * Flow: POST /api/v1/accounts  → returns a hosted web URL for the user
 *       → Jumio redirects back and sends webhooks on completion.
 *
 * Config keys expected (kyc.providers.jumio.*):
 *   api_token        – Jumio OAuth2 client ID
 *   api_secret       – Jumio OAuth2 client secret
 *   base_url         – Regional base URL (e.g. https://account.amer-1.jumio.ai)
 *   success_url      – URL Jumio redirects to on success
 *   error_url        – URL Jumio redirects to on failure
 */
class JumioKycProvider extends BaseKycProvider
{
    protected string $apiToken;
    protected string $apiSecret;
    protected string $baseUrl;
    protected string $successUrl;
    protected string $errorUrl;

    public function __construct()
    {
        $this->apiToken   = config('kyc.providers.jumio.api_token', '');
        $this->apiSecret  = config('kyc.providers.jumio.api_secret', '');
        $this->baseUrl    = rtrim(config('kyc.providers.jumio.base_url', 'https://account.amer-1.jumio.ai'), '/');
        $this->successUrl = config('kyc.providers.jumio.success_url', url('/kyc/verify'));
        $this->errorUrl   = config('kyc.providers.jumio.error_url', url('/kyc/verify'));
    }

    public function name(): string
    {
        return 'jumio';
    }

    public function label(): string
    {
        return 'Jumio';
    }

    /**
     * Create a Jumio account/transaction and return the hosted verification URL.
     */
    public function initiateVerification(KycVerification $verification): array
    {
        $this->assertConfigured();

        $accessToken = $this->getAccessToken();

        $user = $verification->user;

        $response = Http::withToken($accessToken)
            ->baseUrl($this->baseUrl)
            ->post('/api/v1/accounts', [
                'customerInternalReference' => (string) $verification->id,
                'userReference'             => (string) $verification->user_id,
                'callbackUrl'               => url('/webhooks/kyc/jumio'),
                'successUrl'                => $this->successUrl,
                'errorUrl'                  => $this->errorUrl,
                'workflowDefinition'        => ['key' => 10010], // default: Identity Verification
            ]);

        if (! $response->successful()) {
            throw new Exception("Jumio account creation failed: " . $response->body());
        }

        $data = $response->json();

        return [
            'reference' => $data['accountId'] ?? $data['transactionReference'] ?? '',
            'url'       => $data['web']['href'] ?? null,
            'token'     => $data['web']['token'] ?? null,
            'metadata'  => $data,
        ];
    }

    /**
     * Handle a Jumio callback/webhook.
     *
     * Jumio sends a JSON body with a 'decision' object.
     */
    public function handleWebhook(array $payload, KycVerification $verification): void
    {
        $decision = $payload['decision'] ?? [];
        $type     = strtoupper($decision['type'] ?? '');

        match ($type) {
            'PASSED' => $this->markVerified($verification),
            'REJECTED', 'WARNING' => $this->markFailed(
                $verification,
                $decision['details']['label'] ?? 'Identity verification rejected.'
            ),
            'PROCESSING', 'MANUAL_REVIEW' => $this->markReviewing($verification),
            default => null,
        };
    }

    /**
     * Retrieve the status of a Jumio workflow execution.
     */
    public function checkStatus(string $providerReference): array
    {
        $this->assertConfigured();

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->baseUrl($this->baseUrl)
            ->get("/api/v1/accounts/{$providerReference}/workflow-executions");

        if (! $response->successful()) {
            throw new Exception("Jumio status check failed: " . $response->body());
        }

        $executions = $response->json('workflowExecutions', []);
        $latest     = collect($executions)->sortByDesc('startedAt')->first();

        if (! $latest) {
            return ['status' => 'pending', 'raw' => []];
        }

        $decisionType = $latest['decision']['type'] ?? '';

        return [
            'status' => $this->mapStatus($decisionType),
            'raw'    => $latest,
        ];
    }

    public function supportsLiveness(): bool
    {
        return true; // Jumio's Identity Verification includes a biometric step
    }

    public function supportsDocumentCollection(): bool
    {
        return true; // Jumio captures documents in its hosted flow
    }

    protected function mapStatus(string $providerStatus): string
    {
        return match (strtoupper($providerStatus)) {
            'PASSED'                        => 'verified',
            'REJECTED', 'WARNING'           => 'failed',
            'PROCESSING', 'MANUAL_REVIEW'   => 'reviewing',
            default                         => 'pending',
        };
    }

    /**
     * Obtain a short-lived OAuth2 access token from Jumio.
     */
    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->apiToken, $this->apiSecret)
            ->asForm()
            ->post("{$this->baseUrl}/oauth2/token", [
                'grant_type' => 'client_credentials',
            ]);

        if (! $response->successful()) {
            throw new Exception("Jumio OAuth token request failed: " . $response->body());
        }

        return $response->json('access_token');
    }

    private function assertConfigured(): void
    {
        if (empty($this->apiToken) || empty($this->apiSecret)) {
            throw new Exception("Jumio API credentials are not configured.");
        }
    }
}
