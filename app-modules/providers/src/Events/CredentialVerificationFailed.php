<?php

namespace Nucleus\Providers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Nucleus\Providers\Models\ProviderProfile;

class CredentialVerificationFailed
{
    use Dispatchable;

    public function __construct(
        public ProviderProfile $profile,
        public ?string $reason = null,
    ) {
    }
}
