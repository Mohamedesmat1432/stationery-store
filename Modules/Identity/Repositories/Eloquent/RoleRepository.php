<?php

namespace Modules\Identity\Repositories\Eloquent;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;

class RoleRepository extends BaseRepository implements ProtectsBulkResources, RoleRepositoryInterface
{
    use HandlesQueryBuilder;

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
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->applyQueryBuilder(
            model: Role::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
            ],
            perPage: $perPage,
            with: ['permissions'],
            params: $params
        );
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

    public function getProtectedIds(array $ids): array
    {
        return Role::whereIn('id', $ids)
            ->cursor()
            ->filter(fn (Role $role) => $role->isProtected())
            ->pluck('id')
            ->toArray();
    }
}
