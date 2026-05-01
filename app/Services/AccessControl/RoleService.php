<?php

namespace App\Services\AccessControl;

use App\Data\AccessControl\RoleData;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Concerns\HandlesBulkOperations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoleService
{
    use HandlesBulkOperations;

    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {
    }

    public function getRolesPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($perPage);
    }

    public function createRole(RoleData $data): Role
    {
        return DB::transaction(fn() => tap(
            $this->roleRepository->create(['name' => $data->name]),
            function (Role $role) use ($data) {
                $this->roleRepository->syncPermissions($role, $data->permissions);
                User::flushRedisTag();
            }
        ));
    }

    public function updateRole(Role $role, RoleData $data): Role
    {
        return DB::transaction(fn() => tap(
            $this->roleRepository->update($role, ['name' => $data->name]),
            function (Role $role) use ($data) {
                $this->roleRepository->syncPermissions($role, $data->permissions);
                User::flushRedisTag();
            }
        ));
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->name === Role::ROLE_ADMIN) {
            return false;
        }

        $deleted = $this->roleRepository->delete($role);

        if ($deleted) {
            User::flushRedisTag();
        }

        return $deleted;
    }

    public function bulkDelete(array $ids): bool
    {
        // Prevent deleting the admin role
        $ids = Role::whereIn('id', $ids)
            ->where('name', '!=', Role::ROLE_ADMIN)
            ->pluck('id')
            ->toArray();

        $deleted = !empty($ids) && $this->roleRepository->bulkDelete($ids);

        if ($deleted) {
            User::flushRedisTag();
        }

        return $deleted;
    }
}
