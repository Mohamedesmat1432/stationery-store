<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CUSTOMERS->value);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CUSTOMERS->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_CUSTOMERS->value);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_CUSTOMERS->value);
    }

    public function delete(User $user, ?Customer $customer = null): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_CUSTOMERS->value);
    }

    public function restore(User $user, ?Customer $customer = null): bool
    {
        return $user->hasPermissionTo(PermissionName::RESTORE_CUSTOMERS->value);
    }

    public function forceDelete(User $user, ?Customer $customer = null): bool
    {
        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_CUSTOMERS->value);
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::EXPORT_CUSTOMERS->value);
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::IMPORT_CUSTOMERS->value);
    }
}
