<?php

namespace Nucleus\Kyc\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Nucleus\Kyc\Events\KycVerified;
use Nucleus\Kyc\Mail\KycVerifiedMail;

class NotifyUserOfVerification implements ShouldQueue
{
    public string $queue = 'kyc';

    public function handle(KycVerified $event): void
    {
        Mail::to($event->verification->user)->send(new KycVerifiedMail($event->verification));
    }
}
