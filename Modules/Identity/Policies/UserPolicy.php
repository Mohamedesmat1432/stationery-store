<?php

namespace Modules\Identity\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_USERS->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_USERS->value) || $user->id === $model->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_USERS->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update themselves if they have UPDATE_USERS permission
        if ($user->id === $model->id) {
            return $user->hasPermissionTo(PermissionName::UPDATE_USERS->value);
        }

        // Only admins can update other admins
        if ($model->isAdmin() && ! $user->isAdmin()) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::UPDATE_USERS->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ?User $model = null): bool
    {
        if ($model && $model->isProtected($user)) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::DELETE_USERS->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ?User $model = null): bool
    {
        if ($model && $model->isProtected($user)) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::RESTORE_USERS->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ?User $model = null): bool
    {
        if ($model && $model->isProtected($user)) {
            return false;
        }

        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_USERS->value);
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::EXPORT_USERS->value);
    }

    /**
     * Determine whether the user can import models.
     */
    public function import(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::IMPORT_USERS->value);
    }
}
