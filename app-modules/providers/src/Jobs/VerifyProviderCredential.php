<?php

namespace Nucleus\Providers\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Nucleus\Providers\Contracts\CredentialVerifier;
use Nucleus\Providers\Events\CredentialRequiresReview;
use Nucleus\Providers\Events\CredentialVerificationFailed;
use Nucleus\Providers\Events\CredentialVerified;
use Nucleus\Providers\Models\ProviderProfile;
use Nucleus\Providers\Notifications\CredentialVerificationResultNotification;

class VerifyProviderCredential implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ProviderProfile $profile)
    {
    }

    public function handle(CredentialVerifier $verifier): void
    {
        $reference = $this->profile->credential_reference;

        if (! $reference) {
            $this->profile->update([
                'verification_status'  => ProviderProfile::STATUS_MANUAL_REVIEW,
                'verification_message' => 'No credential reference provided.',
            ]);
            CredentialRequiresReview::dispatch($this->profile, 'No credential reference provided.');
            $this->notify();
            return;
        }

        $result = $verifier->verify($reference, $this->profile);

        $this->profile->update([
            'verification_status'  => $result['status'],
            'verification_message' => $result['message'] ?? null,
            'verified_at'          => $result['status'] === ProviderProfile::STATUS_VERIFIED ? now() : null,
            'metadata'             => array_merge($this->profile->metadata ?? [], [
                'last_verification_payload' => $result['payload'] ?? [],
            ]),
        ]);

        match ($result['status']) {
            ProviderProfile::STATUS_VERIFIED      => CredentialVerified::dispatch($this->profile),
            ProviderProfile::STATUS_FAILED        => CredentialVerificationFailed::dispatch($this->profile, $result['message'] ?? null),
            ProviderProfile::STATUS_MANUAL_REVIEW => CredentialRequiresReview::dispatch($this->profile, $result['message'] ?? null),
            default                               => null,
        };

        $this->notify();
    }

    private function notify(): void
    {
        if ($user = $this->profile->user) {
            Notification::send($user, new CredentialVerificationResultNotification($this->profile));
        }
    }
}
