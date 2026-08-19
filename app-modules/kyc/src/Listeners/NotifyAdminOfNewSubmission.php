<?php

namespace Nucleus\Kyc\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Nucleus\Kyc\Events\KycSubmittedForReview;
use Nucleus\Kyc\Mail\KycSubmittedAdminMail;

class NotifyAdminOfNewSubmission implements ShouldQueue
{
    public string $queue = 'kyc';

    public function handle(KycSubmittedForReview $event): void
    {
        $adminEmail = config('kyc.admin_notification_email', 'compliance@example.com');

        Mail::to($adminEmail)->send(new KycSubmittedAdminMail($event->verification));
    }
}
