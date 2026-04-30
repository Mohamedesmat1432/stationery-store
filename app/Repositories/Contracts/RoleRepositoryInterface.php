<?php

namespace App\Repositories\Contracts;

use App\Models\Role;

interface RoleRepositoryInterface extends RepositoryInterface
{
    public function syncPermissions(Role $role, array $permissions): void;
}
