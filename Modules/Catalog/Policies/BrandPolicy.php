<?php

namespace Modules\Catalog\Policies;

use App\Models\Brand;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class BrandPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_BRANDS->value);
    }

    public function view(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_BRANDS->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_BRANDS->value);
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_BRANDS->value);
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_BRANDS->value);
    }

    public function restore(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionName::RESTORE_BRANDS->value);
    }

    public function forceDelete(User $user, Brand $brand): bool
    {
        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_BRANDS->value);
    }

    /**
     * Determine whether the user can perform bulk actions.
     */
    public function bulkAction(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_BRANDS->value)
            || $user->hasPermissionTo(PermissionName::RESTORE_BRANDS->value)
            || $user->hasPermissionTo(PermissionName::FORCE_DELETE_BRANDS->value);
    }
}
