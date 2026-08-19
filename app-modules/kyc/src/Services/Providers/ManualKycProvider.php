<?php

namespace Nucleus\Kyc\Services\Providers;

use Nucleus\Kyc\Models\KycVerification;

/**
 * ManualKycProvider handles the in-app document upload + liveness check flow.
 * No external API calls are made; verification is driven by the time-based
 * state machine already present on the KycVerification model.
 */
class ManualKycProvider extends BaseKycProvider
{
    public function name(): string
    {
        return 'manual';
    }

    public function label(): string
    {
        return 'Manual Review';
    }

    /**
     * For the manual provider the "session" is simply the local verification
     * record itself — no external service is involved.
     */
    public function initiateVerification(KycVerification $verification): array
    {
        return [
            'reference' => (string) $verification->id,
            'url'       => null,
            'token'     => null,
            'metadata'  => [],
        ];
    }

    /**
     * The manual provider does not receive webhooks from an external service.
     * Status is advanced via polling in KycStatus Livewire component.
     */
    public function handleWebhook(array $payload, KycVerification $verification): void
    {
        // No-op: state is advanced through KycVerification::advance()
    }

    /**
     * Returns the current normalised status based on the local record.
     */
    public function checkStatus(string $providerReference): array
    {
        $verification = KycVerification::find((int) $providerReference);

        if (! $verification) {
            return ['status' => 'pending', 'raw' => []];
        }

        return [
            'status' => $this->mapStatus($verification->status),
            'raw'    => ['status' => $verification->status],
        ];
    }

    public function supportsLiveness(): bool
    {
        return false; // liveness is collected manually via the in-app recorder
    }

    public function supportsDocumentCollection(): bool
    {
        return false; // documents are uploaded manually via the in-app uploader
    }

    protected function mapStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            'verified'                      => 'verified',
            'failed', 'failed_submitted'    => 'failed',
            'identity_verifying',
            'identity_slow'                 => 'reviewing',
            default                         => 'pending',
        };
    }
}
