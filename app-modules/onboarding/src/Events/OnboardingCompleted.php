<?php

namespace Nucleus\Onboarding\Events;

use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Nucleus\Onboarding\Models\OnboardingSubmission;

/**
 * Dispatched when a user finishes the onboarding wizard. Forks attach
 * listeners for industry-specific post-onboarding work (e.g. assigning a
 * primary provider, creating domain records from the wizard's metadata
 * blob, sending welcome notifications).
 */
class OnboardingCompleted
{
    use Dispatchable;

    public function __construct(
        public User $user,
        public OnboardingSubmission $submission,
    ) {
    }
}
