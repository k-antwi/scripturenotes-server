<?php

namespace Nucleus\Kyc\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Nucleus\Kyc\Models\KycVerification;

class KycMoreInfoRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly KycVerification $verification
    ) {}
}
