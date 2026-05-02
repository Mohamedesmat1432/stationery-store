<?php

namespace Modules\CRM\Policies;

use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Identity\Enums\PermissionName;

class CustomerGroupPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CUSTOMER_GROUPS->value);
    }

    public function view(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->hasPermissionTo(PermissionName::VIEW_CUSTOMER_GROUPS->value);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::CREATE_CUSTOMER_GROUPS->value);
    }

    public function update(User $user, CustomerGroup $customerGroup): bool
    {
        return $user->hasPermissionTo(PermissionName::UPDATE_CUSTOMER_GROUPS->value);
    }

    public function delete(User $user, ?CustomerGroup $customerGroup = null): bool
    {
        return $user->hasPermissionTo(PermissionName::DELETE_CUSTOMER_GROUPS->value);
    }

    public function restore(User $user, ?CustomerGroup $customerGroup = null): bool
    {
        return $user->hasPermissionTo(PermissionName::RESTORE_CUSTOMER_GROUPS->value);
    }

    public function forceDelete(User $user, ?CustomerGroup $customerGroup = null): bool
    {
        return $user->hasPermissionTo(PermissionName::FORCE_DELETE_CUSTOMER_GROUPS->value);
    }

    public function export(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::EXPORT_CUSTOMER_GROUPS->value);
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo(PermissionName::IMPORT_CUSTOMER_GROUPS->value);
    }
}
