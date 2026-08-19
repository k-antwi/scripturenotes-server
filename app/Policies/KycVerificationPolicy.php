<?php

namespace App\Policies;

use App\Models\User;
use Nucleus\Kyc\Models\KycVerification;
use Illuminate\Auth\Access\HandlesAuthorization;

class KycVerificationPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_kyc_verification');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, KycVerification $model): bool
    {
        return $user->can('view_kyc_verification');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_kyc_verification');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, KycVerification $model): bool
    {
        return $user->can('update_kyc_verification');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, KycVerification $model): bool
    {
        return $user->can('delete_kyc_verification');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, KycVerification $model): bool
    {
        return $user->can('restore_kyc_verification');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, KycVerification $model): bool
    {
        return $user->can('force_delete_kyc_verification');
    }
}
