<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_ORDERS->value);
    }

    public function view(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_ORDERS->value) || $user->id === $order->user_id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_ORDERS->value);
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_ORDERS->value);
    }
}
