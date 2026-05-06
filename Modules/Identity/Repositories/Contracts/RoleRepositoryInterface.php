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
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Sync permissions to a role.
     *
     * @param  array<string>  $permissions
     */
    public function syncPermissions(Role $role, array $permissions): void;

    /**
     * Get all role names available in the system.
     *
     * @return array<string>
     */
    public function getAvailableNames(): array;

    /**
     * Get permissions for a specific role ID.
     *
     * @return array<string>
     */
    public function getPermissions(string $roleId): array;
}
