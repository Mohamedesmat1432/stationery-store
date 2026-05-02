<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Data\RoleData;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;

class RoleService
{
    use HandlesBulkOperations;

    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    public function getRolesPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->roleRepository->paginate($perPage);
    }

    public function createRole(RoleData $data): Role
    {
        return DB::transaction(fn () => tap(
            $this->roleRepository->create(['name' => $data->name]),
            function (Role $role) use ($data) {
                $this->roleRepository->syncPermissions($role, $data->permissions);
                // Cache invalidation handled by RoleObserver::saved()
            }
        ));
    }

    public function updateRole(Role $role, RoleData $data): Role
    {
        return DB::transaction(fn () => tap(
            $this->roleRepository->update($role, ['name' => $data->name]),
            function (Role $role) use ($data) {
                $this->roleRepository->syncPermissions($role, $data->permissions);
                // Cache invalidation handled by RoleObserver::saved()
            }
        ));
    }

    public function deleteRole(Role $role): bool
    {
        if ($role->name === Role::ROLE_ADMIN) {
            return false;
        }

        return $this->roleRepository->delete($role);
    }

    public function bulkDelete(array $ids): bool
    {
        // Prevent deleting the admin role
        $ids = Role::whereIn('id', $ids)
            ->where('name', '!=', Role::ROLE_ADMIN)
            ->pluck('id')
            ->toArray();

        return ! empty($ids) && $this->roleRepository->bulkDelete($ids);
    }
}
