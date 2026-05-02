<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class ProductPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_PRODUCTS->value);
    }

    public function view(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_PRODUCTS->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_PRODUCTS->value);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_PRODUCTS->value);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_PRODUCTS->value);
    }
}
