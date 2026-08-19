<?php

namespace Nucleus\Kyc\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;

/**
 * Veriff KYC provider.
 *
 * Docs: https://developers.veriff.com/
 *
 * Flow: POST /v1/sessions → returns a hosted verification URL
 *       → Veriff sends webhooks on decision events.
 *
 * Config keys expected (kyc.providers.veriff.*):
 *   api_key        – Veriff API key
 *   api_secret     – Veriff API secret (used for HMAC webhook signature)
 *   base_url       – Base URL (default: https://stationapi.veriff.com)
 *
 * Veriff decision codes:
 *   9001 = approved
 *   9102 = declined
 *   9103 = resubmission_requested
 *   9104 = review
 */
class VeriffKycProvider extends BaseKycProvider
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey    = config('kyc.providers.veriff.api_key', '');
        $this->apiSecret = config('kyc.providers.veriff.api_secret', '');
        $this->baseUrl   = rtrim(config('kyc.providers.veriff.base_url', 'https://stationapi.veriff.com'), '/');
    }

    public function name(): string
    {
        return 'veriff';
    }

    public function label(): string
    {
        return 'Veriff';
    }

    /**
     * Create a Veriff session and return the hosted verification URL.
     */
    public function initiateVerification(KycVerification $verification): array
    {
        $this->assertConfigured();

        $user = $verification->user;

        $body = [
            'verification' => [
                'callback'   => url('/webhooks/kyc/veriff'),
                'person'     => [
                    'firstName' => $user->first_name ?? null,
                    'lastName'  => $user->last_name ?? null,
                ],
                'vendorData' => (string) $verification->id,
                'timestamp'  => now()->toIso8601String(),
            ],
        ];

        $response = $this->client()->post('/v1/sessions', $body);

        if (! $response->successful()) {
            throw new Exception("Veriff session creation failed: " . $response->body());
        }

        $data = $response->json('verification', []);

        return [
            'reference' => $data['id'] ?? '',
            'url'       => $data['url'] ?? null,
            'token'     => null,
            'metadata'  => $data,
        ];
    }

    /**
     * Handle a Veriff webhook event.
     *
     * Relevant events: verification.decision, session.approved, session.declined
     */
    public function handleWebhook(array $payload, KycVerification $verification): void
    {
        $decision = $payload['verification'] ?? [];
        $code     = (int) ($decision['code'] ?? 0);
        $status   = $decision['status'] ?? '';

        if ($code === 9001 || $status === 'approved') {
            $this->markVerified($verification);
        } elseif (in_array($code, [9102, 9103]) || $status === 'declined') {
            $this->markFailed($verification, $decision['reason'] ?? 'Identity verification declined.');
        } elseif ($code === 9104 || $status === 'review') {
            $this->markReviewing($verification);
        }
    }

    /**
     * Retrieve the decision for an existing Veriff session.
     */
    public function checkStatus(string $providerReference): array
    {
        $this->assertConfigured();

        $response = $this->client()->get("/v1/sessions/{$providerReference}/decision");

        if ($response->status() === 404) {
            return ['status' => 'pending', 'raw' => []];
        }

        if (! $response->successful()) {
            throw new Exception("Veriff status check failed: " . $response->body());
        }

        $data   = $response->json('verification', []);
        $code   = (int) ($data['code'] ?? 0);
        $status = $this->resolveStatusFromCode($code) ?: $this->mapStatus($data['status'] ?? '');

        return [
            'status' => $status,
            'raw'    => $data,
        ];
    }

    public function supportsLiveness(): bool
    {
        return true; // Veriff includes biometric face match + liveness
    }

    public function supportsDocumentCollection(): bool
    {
        return true; // Veriff captures documents in its hosted flow
    }

    protected function mapStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'approved' => 'verified',
            'declined' => 'failed',
            'review'   => 'reviewing',
            default    => 'pending',
        };
    }

    private function resolveStatusFromCode(int $code): ?string
    {
        return match ($code) {
            9001            => 'verified',
            9102, 9103      => 'failed',
            9104            => 'reviewing',
            default         => null,
        };
    }

    private function client()
    {
        return Http::withHeaders([
            'X-AUTH-CLIENT' => $this->apiKey,
            'Content-Type'  => 'application/json',
        ])
            ->baseUrl($this->baseUrl)
            ->timeout(15);
    }

    private function assertConfigured(): void
    {
        if (empty($this->apiKey)) {
            throw new Exception("Veriff API key is not configured.");
        }
    }
}
