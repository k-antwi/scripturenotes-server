<?php

namespace Nucleus\Brain\Ai\Tools;

use App\Models\User;
use Laravel\Ai\Attributes\Tool;

class GetProviderProfile
{
    public function __construct(protected User $user) {}

    #[Tool('Get the provider profile linked to the currently authenticated user, if any')]
    public function __invoke(): array
    {
        $profile = $this->user->providerProfile;

        if (! $profile) {
            return ['linked_provider' => null];
        }

        return [
            'id'                  => $profile->id,
            'credential_reference' => $profile->credential_reference,
            'verification_status' => $profile->verification_status,
            'bio'                 => $profile->bio,
            'is_active'           => $profile->is_active,
        ];
    }
}
