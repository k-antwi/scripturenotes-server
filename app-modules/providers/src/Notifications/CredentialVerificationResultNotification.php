<?php

namespace Nucleus\Providers\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Nucleus\Providers\Models\ProviderProfile;

class CredentialVerificationResultNotification extends Notification
{
    use Queueable;

    public function __construct(public ProviderProfile $profile)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = ProviderProfile::statusOptions()[$this->profile->verification_status] ?? $this->profile->verification_status;

        return (new MailMessage)
            ->subject("Provider credential: {$status}")
            ->line("Your provider credential verification status is: **{$status}**.")
            ->when($this->profile->verification_message, fn ($mail, $msg) => $mail->line($msg));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'profile_id' => $this->profile->id,
            'status'     => $this->profile->verification_status,
            'message'    => $this->profile->verification_message,
        ];
    }
}
