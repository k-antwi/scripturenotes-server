<?php

namespace Nucleus\Kyc\Services\Providers;

use Exception;
use Illuminate\Support\Facades\Http;
use Nucleus\Kyc\Models\KycVerification;

/**
 * Stripe Identity KYC provider.
 *
 * Docs: https://stripe.com/docs/identity
 *
 * Config keys expected (services.stripe_identity.*):
 *   secret_key   – Stripe secret API key (sk_live_… / sk_test_…)
 *   webhook_secret – Stripe webhook signing secret (whsec_…)
 *   return_url   – URL Stripe redirects the user to after the hosted flow
 */
class StripeKycProvider extends BaseKycProvider
{
    protected string $secretKey;
    protected string $webhookSecret;
    protected string $returnUrl;
    protected string $baseUrl = 'https://api.stripe.com/v1';

    public function __construct()
    {
        $this->secretKey     = config('kyc.providers.stripe.secret_key', '');
        $this->webhookSecret = config('kyc.providers.stripe.webhook_secret', '');
        $this->returnUrl     = config('kyc.providers.stripe.return_url', url('/kyc/verify'));
    }

    public function name(): string
    {
        return 'stripe';
    }

    public function label(): string
    {
        return 'Stripe Identity';
    }

    /**
     * Create a Stripe Identity VerificationSession and return the hosted URL.
     */
    public function initiateVerification(KycVerification $verification): array
    {
        $this->assertConfigured();

        $response = Http::withBasicAuth($this->secretKey, '')
            ->asForm()
            ->post("{$this->baseUrl}/identity/verification_sessions", [
                'type'       => 'document',
                'return_url' => $this->returnUrl,
                'metadata'   => [
                    'user_id'             => $verification->user_id,
                    'kyc_verification_id' => $verification->id,
                ],
            ]);

        if (! $response->successful()) {
            throw new Exception("Stripe Identity session creation failed: " . $response->body());
        }

        $data = $response->json();

        return [
            'reference' => $data['id'],
            'url'       => $data['url'],
            'token'     => $data['client_secret'] ?? null,
            'metadata'  => $data,
        ];
    }

    /**
     * Handle a Stripe Identity webhook event.
     *
     * Expected events:
     *   identity.verification_session.verified
     *   identity.verification_session.requires_input  (failed / needs more info)
     */
    public function handleWebhook(array $payload, KycVerification $verification): void
    {
        $eventType = $payload['type'] ?? '';
        $session   = $payload['data']['object'] ?? [];

        match ($eventType) {
            'identity.verification_session.verified' => $this->markVerified($verification),

            'identity.verification_session.requires_input' => $this->markFailed(
                $verification,
                $this->extractFailureReason($session)
            ),

            'identity.verification_session.processing' => $this->markReviewing($verification),

            default => null,
        };
    }

    /**
     * Retrieve the current status of a VerificationSession from Stripe.
     */
    public function checkStatus(string $providerReference): array
    {
        $this->assertConfigured();

        $response = Http::withBasicAuth($this->secretKey, '')
            ->get("{$this->baseUrl}/identity/verification_sessions/{$providerReference}");

        if (! $response->successful()) {
            throw new Exception("Stripe Identity status check failed: " . $response->body());
        }

        $data = $response->json();

        return [
            'status' => $this->mapStatus($data['status'] ?? 'processing'),
            'raw'    => $data,
        ];
    }

    public function supportsLiveness(): bool
    {
        return true; // Stripe Identity includes a selfie/liveness step
    }

    public function supportsDocumentCollection(): bool
    {
        return true; // Stripe handles document capture in its hosted flow
    }

    protected function mapStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'verified'        => 'verified',
            'requires_input'  => 'failed',
            'processing'      => 'reviewing',
            default           => 'pending',
        };
    }

    private function extractFailureReason(array $session): string
    {
        $error = $session['last_error'] ?? [];

        return $error['reason'] ?? $error['code'] ?? 'Identity verification requires additional input.';
    }

    private function assertConfigured(): void
    {
        if (empty($this->secretKey)) {
            throw new Exception("Stripe Identity secret key is not configured.");
        }
    }
}
