<?php

namespace Nucleus\Kyc\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Nucleus\Kyc\Models\KycVerification;

class KycFailedMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public readonly KycVerification $verification
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your identity verification was unsuccessful',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'kyc::mail.kyc-failed',
        );
    }
}
