<?php

namespace Nucleus\Providers\Verifiers;

use Nucleus\Providers\Contracts\CredentialVerifier;
use Nucleus\Providers\Models\ProviderProfile;

class NullCredentialVerifier implements CredentialVerifier
{
    public function verify(string $credentialReference, ProviderProfile $profile): array
    {
        return [
            'status'  => 'manual_review',
            'message' => 'No verifier configured. Bind a CredentialVerifier in providers.php to enable automatic verification.',
            'payload' => [],
        ];
    }
}
