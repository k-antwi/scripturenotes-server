<?php

namespace App\Policies;

use App\Models\User;
use Nucleus\Onboarding\Models\OnboardingSubmission;
use Illuminate\Auth\Access\HandlesAuthorization;

class OnboardingSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_onboarding_submission');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OnboardingSubmission $model): bool
    {
        return $user->can('view_onboarding_submission');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_onboarding_submission');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OnboardingSubmission $model): bool
    {
        return $user->can('update_onboarding_submission');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OnboardingSubmission $model): bool
    {
        return $user->can('delete_onboarding_submission');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OnboardingSubmission $model): bool
    {
        return $user->can('restore_onboarding_submission');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OnboardingSubmission $model): bool
    {
        return $user->can('force_delete_onboarding_submission');
    }
}
