<?php

namespace Modules\Identity\Repositories\Contracts;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface RoleRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated roles with filtering.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function syncPermissions(Role $role, array $permissions): void;
}
