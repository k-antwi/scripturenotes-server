<?php

namespace Nucleus\Kyc\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;

/**
 * Onfido KYC provider.
 *
 * Docs: https://documentation.onfido.com/
 *
 * Flow: Create applicant → Create SDK token (for embedded SDK) or workflow run
 *       → Receive webhook when check is complete.
 *
 * Config keys expected (kyc.providers.onfido.*):
 *   api_token      – Onfido API token
 *   webhook_token  – Token used to verify webhook signatures
 *   region         – 'eu' | 'us' | 'ca'  (default: 'eu')
 *   workflow_id    – Onfido Studio workflow ID (optional; falls back to classic checks)
 */
class OnfidoKycProvider extends BaseKycProvider
{
    protected string $apiToken;
    protected string $webhookToken;
    protected string $region;
    protected ?string $workflowId;

    public function __construct()
    {
        $this->apiToken     = config('kyc.providers.onfido.api_token', '');
        $this->webhookToken = config('kyc.providers.onfido.webhook_token', '');
        $this->region       = config('kyc.providers.onfido.region', 'eu');
        $this->workflowId   = config('kyc.providers.onfido.workflow_id');
    }

    public function name(): string
    {
        return 'onfido';
    }

    public function label(): string
    {
        return 'Onfido';
    }

    /**
     * Create an Onfido applicant and return an SDK token for the embedded SDK.
     */
    public function initiateVerification(KycVerification $verification): array
    {
        $this->assertConfigured();

        $user = $verification->user;

        // Step 1: Create applicant
        $applicantResponse = $this->client()->post('applicants', [
            'first_name' => $user->first_name ?? 'Unknown',
            'last_name'  => $user->last_name ?? 'Unknown',
            'email'      => $user->email,
        ]);

        if (! $applicantResponse->successful()) {
            throw new Exception("Onfido applicant creation failed: " . $applicantResponse->body());
        }

        $applicantId = $applicantResponse->json('id');

        // Step 2: Generate SDK token for the embedded Web SDK
        $tokenResponse = $this->client()->post('sdk_token', [
            'applicant_id' => $applicantId,
            'referrer'     => url('*'),
        ]);

        if (! $tokenResponse->successful()) {
            throw new Exception("Onfido SDK token generation failed: " . $tokenResponse->body());
        }

        return [
            'reference' => $applicantId,
            'url'       => null,
            'token'     => $tokenResponse->json('token'),
            'metadata'  => [
                'applicant_id' => $applicantId,
            ],
        ];
    }

    /**
     * Handle an Onfido webhook event.
     *
     * Relevant event actions: check.completed, workflow_run.completed
     */
    public function handleWebhook(array $payload, KycVerification $verification): void
    {
        $action  = $payload['payload']['action'] ?? '';
        $object  = $payload['payload']['object'] ?? [];
        $result  = $object['result'] ?? $object['status'] ?? '';

        match ($action) {
            'check.completed' => $this->handleCheckCompleted($result, $verification),
            'workflow_run.completed' => $this->handleWorkflowCompleted($object, $verification),
            default => null,
        };
    }

    /**
     * Retrieve check status for an applicant via the Onfido API.
     * Uses the applicant ID stored as the provider reference.
     */
    public function checkStatus(string $providerReference): array
    {
        $this->assertConfigured();

        // List checks for this applicant and return the most recent
        $response = $this->client()->get("checks?applicant_id={$providerReference}");

        if (! $response->successful()) {
            throw new Exception("Onfido status check failed: " . $response->body());
        }

        $checks = $response->json('checks', []);
        $latest = collect($checks)->sortByDesc('created_at')->first();

        if (! $latest) {
            return ['status' => 'pending', 'raw' => []];
        }

        return [
            'status' => $this->mapStatus($latest['result'] ?? $latest['status'] ?? ''),
            'raw'    => $latest,
        ];
    }

    public function supportsLiveness(): bool
    {
        return true; // Onfido's facial similarity check provides liveness detection
    }

    public function supportsDocumentCollection(): bool
    {
        return true; // Onfido handles document capture via its SDK
    }

    protected function mapStatus(string $providerStatus): string
    {
        return match (strtolower($providerStatus)) {
            'clear', 'approved'     => 'verified',
            'consider', 'rejected'  => 'failed',
            'in_progress', 'awaiting_applicant' => 'reviewing',
            default                 => 'pending',
        };
    }

    private function handleCheckCompleted(string $result, KycVerification $verification): void
    {
        match (strtolower($result)) {
            'clear' => $this->markVerified($verification),
            'consider' => $this->markFailed($verification, 'Identity check returned "consider" result.'),
            default => null,
        };
    }

    private function handleWorkflowCompleted(array $object, KycVerification $verification): void
    {
        $status = $object['status'] ?? '';
        match (strtolower($status)) {
            'approved' => $this->markVerified($verification),
            'declined', 'rejected' => $this->markFailed(
                $verification,
                $object['reasons'][0] ?? 'Workflow declined.'
            ),
            default => $this->markReviewing($verification),
        };
    }

    private function client()
    {
        $baseUrls = [
            'eu' => 'https://api.eu.onfido.com/v3.6/',
            'us' => 'https://api.us.onfido.com/v3.6/',
            'ca' => 'https://api.ca.onfido.com/v3.6/',
        ];

        return Http::withToken($this->apiToken)
            ->withHeaders(['Onfido-Version' => '2019-09-05'])
            ->baseUrl($baseUrls[$this->region] ?? $baseUrls['eu'])
            ->timeout(15);
    }

    private function assertConfigured(): void
    {
        if (empty($this->apiToken)) {
            throw new Exception("Onfido API token is not configured.");
        }
    }
}
