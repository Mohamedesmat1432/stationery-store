<?php

namespace Modules\Identity\Policies;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class RolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_ROLES->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_ROLES->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_ROLES->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ?Role $role = null): bool
    {
        if ($role && $role->isProtected()) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::UPDATE_ROLES->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?Role $role = null): bool
    {
        if ($role && $role->isProtected()) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::DELETE_ROLES->value);
    }
}
