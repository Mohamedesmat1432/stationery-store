<?php

namespace App\Services\AccessControl;

use App\Data\AccessControl\RoleData;
use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoleService
{

    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {
    }

    public function getRolesPaginated(int $perPage = 15): LengthAwarePaginator
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

    public function createRole(RoleData $data): Role
    {
        return DB::transaction(fn() => tap(
            $this->roleRepository->create(['name' => $data->name]),
            fn(Role $role) => $this->roleRepository->syncPermissions($role, $data->permissions)
        ));
    }

    public function updateRole(Role $role, RoleData $data): Role
    {
        return DB::transaction(fn() => tap(
            $this->roleRepository->update($role, ['name' => $data->name]),
            fn(Role $role) => $this->roleRepository->syncPermissions($role, $data->permissions)
        ));
    }

    public function deleteRole(Role $role): bool
    {
        return $this->roleRepository->delete($role);
    }

    public function bulkDeleteRoles(array $ids): bool
    {
        $filteredIds = Role::whereIn('id', $ids)
            ->pluck('id')
            ->toArray();

        return !empty($filteredIds) && $this->roleRepository->bulkDelete($filteredIds);
    }
}
