<?php

namespace Nucleus\Providers\Contracts;

use Nucleus\Providers\Models\ProviderProfile;

interface CredentialVerifier
{
    /**
     * Verify a provider's credential reference against an external authority.
     *
     * Implementations return one of: 'verified', 'failed', 'manual_review'
     * along with an optional human-readable message.
     *
     * @return array{status: string, message: string|null, payload: array}
     */
    public function verify(string $credentialReference, ProviderProfile $profile): array;
}
