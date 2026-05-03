<?php

namespace Modules\CRM\Policies;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class CustomerPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any customers.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can view the customer.
     */
    public function view(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can create customers.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can update the customer.
     */
    public function update(User $user, Customer $customer): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can delete the customer.
     */
    public function delete(User $user, ?Customer $customer = null): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can restore the customer.
     */
    public function restore(User $user, ?Customer $customer = null): bool
    {
        return $user->hasPermissionTo(PermissionName::RESTORE_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can permanently delete the customer.
     */
    public function forceDelete(User $user, ?Customer $customer = null): bool
    {
        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can export customers.
     */
    public function export(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::EXPORT_CUSTOMERS->value);
    }

    /**
     * Determine whether the user can import customers.
     */
    public function import(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::IMPORT_CUSTOMERS->value);
    }
}
