<?php

namespace Modules\Catalog\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CATEGORIES->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CATEGORIES->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_CATEGORIES->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_CATEGORIES->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_CATEGORIES->value);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionName::RESTORE_CATEGORIES->value);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_CATEGORIES->value);
    }

    /**
     * Determine whether the user can perform bulk actions.
     */
    public function bulkAction(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_CATEGORIES->value)
            || $user->hasPermissionTo(PermissionName::RESTORE_CATEGORIES->value)
            || $user->hasPermissionTo(PermissionName::FORCE_DELETE_CATEGORIES->value);
    }
}
