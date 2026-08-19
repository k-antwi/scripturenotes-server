<?php

namespace Nucleus\Kyc\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Nucleus\Kyc\Events\KycMoreInfoRequested;
use Nucleus\Kyc\Mail\KycMoreInfoNeededMail;

class NotifyUserMoreInfoNeeded implements ShouldQueue
{
    public string $queue = 'kyc';

    public function handle(KycMoreInfoRequested $event): void
    {
        Mail::to($event->verification->user)->send(new KycMoreInfoNeededMail($event->verification));
    }
}
