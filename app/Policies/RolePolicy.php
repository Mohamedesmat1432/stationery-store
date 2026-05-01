<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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

    public function update(User $user, ?Role $role = null): bool
    {
        if ($role && $role->name === Role::ROLE_ADMIN) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::UPDATE_ROLES->value);
    }

    public function delete(User $user, ?Role $role = null): bool
    {
        if ($role && $role->name === Role::ROLE_ADMIN) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::DELETE_ROLES->value);
    }
}
