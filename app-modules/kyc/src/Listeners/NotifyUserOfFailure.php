<?php

namespace Nucleus\Kyc\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Nucleus\Kyc\Events\KycFailed;
use Nucleus\Kyc\Mail\KycFailedMail;

class NotifyUserOfFailure implements ShouldQueue
{
    public string $queue = 'kyc';

    public function handle(KycFailed $event): void
    {
        Mail::to($event->verification->user)->send(new KycFailedMail($event->verification));
    }
}
