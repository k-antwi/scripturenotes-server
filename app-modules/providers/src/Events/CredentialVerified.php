<?php

namespace Nucleus\Providers\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Nucleus\Providers\Models\ProviderProfile;

class CredentialVerified
{
    use Dispatchable;

    public function __construct(public ProviderProfile $profile)
    {
    }
}
