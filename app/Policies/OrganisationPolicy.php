<?php

namespace App\Policies;

use App\Models\User;
use Nucleus\Organisations\Models\Organisation;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrganisationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_organisation');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Organisation $model): bool
    {
        return $user->can('view_organisation');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_organisation');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Organisation $model): bool
    {
        return $user->can('update_organisation');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Organisation $model): bool
    {
        return $user->can('delete_organisation');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Organisation $model): bool
    {
        return $user->can('restore_organisation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Organisation $model): bool
    {
        return $user->can('force_delete_organisation');
    }
}
