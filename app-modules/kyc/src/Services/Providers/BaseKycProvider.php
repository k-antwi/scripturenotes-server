<?php

namespace Nucleus\Kyc\Services\Providers;

use Nucleus\Kyc\Contracts\KycProviderInterface;
use Nucleus\Kyc\Models\KycVerification;

abstract class BaseKycProvider implements KycProviderInterface
{
    /**
     * Map a provider-specific status string to one of the normalised values:
     * pending | reviewing | verified | failed
     */
    abstract protected function mapStatus(string $providerStatus): string;

    /**
     * Mark the KycVerification as verified via this provider.
     */
    protected function markVerified(KycVerification $verification): void
    {
        $verification->update([
            'status'               => 'verified',
            'identity_verified_at' => now(),
        ]);
    }

    /**
     * Mark the KycVerification as failed via this provider.
     */
    protected function markFailed(KycVerification $verification, string $reason = ''): void
    {
        $verification->update([
            'status'         => 'failed',
            'failure_reason' => $reason ?: 'Identity check failed.',
            'failed_at'      => now(),
        ]);
    }

    /**
     * Mark the KycVerification as under review (identity_verifying).
     */
    protected function markReviewing(KycVerification $verification): void
    {
        $verification->update(['status' => 'identity_verifying']);
    }
}
