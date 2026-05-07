<?php

namespace Modules\Catalog\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any products.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_PRODUCTS->value);
    }

    /**
     * Determine whether the user can view the product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_PRODUCTS->value);
    }

    /**
     * Determine whether the user can create products.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_PRODUCTS->value);
    }

    /**
     * Determine whether the user can update the product.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_PRODUCTS->value);
    }

    /**
     * Determine whether the user can delete the product.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_PRODUCTS->value);
    }

    /**
     * Determine whether the user can restore the product.
     */
    public function restore(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::RESTORE_PRODUCTS->value);
    }

    /**
     * Determine whether the user can permanently delete the product.
     */
    public function forceDelete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_PRODUCTS->value);
    }

    /**
     * Determine whether the user can export products.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::EXPORT_PRODUCTS->value);
    }

    /**
     * Determine whether the user can import products.
     */
    public function import(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::IMPORT_PRODUCTS->value);
    }

    /**
     * Determine whether the user can perform bulk actions.
     */
    public function bulkAction(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_PRODUCTS->value)
            || $user->hasPermissionTo(PermissionName::RESTORE_PRODUCTS->value)
            || $user->hasPermissionTo(PermissionName::FORCE_DELETE_PRODUCTS->value);
    }
}
