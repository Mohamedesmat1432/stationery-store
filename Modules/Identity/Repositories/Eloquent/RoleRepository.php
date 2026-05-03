<?php

namespace Modules\Identity\Repositories\Eloquent;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoleRepository extends BaseRepository implements ProtectsBulkResources, RoleRepositoryInterface
{
    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return Role::class;
    }

    /**
     * Get paginated roles with filters and eager loading.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Role::class)
            ->with('permissions')
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a role by ID with permissions.
     */
    public function findById(string $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    /**
     * Sync permissions to the given role.
     */
    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }

    /**
     * Get names of all available roles.
     */
    public function getAvailableNames(): array
    {
        return Role::pluck('name')->toArray();
    }

    /**
     * Get permission names for a role.
     */
    public function getPermissions(string $roleId): array
    {
        return Role::with('permissions')->find($roleId)?->permissions->pluck('name')->toArray() ?? [];
    }

    /**
     * Get IDs from the given set that are protected.
     */
    public function getProtectedIds(array $ids): array
    {
        return Role::whereIn('id', $ids)
            ->where('name', Role::ROLE_ADMIN)
            ->pluck('id')
            ->toArray();
    }
}
