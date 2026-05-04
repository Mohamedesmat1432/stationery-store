<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Data\RoleData;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;

class RoleService
{
    use HandlesBulkOperations, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected RoleRepositoryInterface $roleRepository
    ) {}

    protected function getRepository(): RoleRepositoryInterface
    {
        return $this->roleRepository;
    }

    protected function getModelClass(): string
    {
        return Role::class;
    }

    /**
     * Get paginated roles for the index view.
     */
    public function getRolesPaginated(array $params = [], int $perPage = 15): array
    {
        return IdentityCacheService::rememberRoles(
            $params,
            $perPage,
            fn () => $this->roleRepository->paginate($perPage)
        );
    }

    /**
     * Create a new role with permissions.
     */
    public function createRole(RoleData $data): Role
    {
        return DB::transaction(fn () => tap(
            $this->roleRepository->create(['name' => $data->name]),
            function (Role $role) use ($data) {
                $this->roleRepository->syncPermissions($role, $data->permissions);
            }
        ));
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Role $role, RoleData $data): Role
    {
        return DB::transaction(fn () => tap(
            $this->roleRepository->update($role, ['name' => $data->name]),
            function (Role $role) use ($data) {
                $this->roleRepository->syncPermissions($role, $data->permissions);
            }
        ));
    }

    /**
     * Delete a role if not protected.
     */
    public function deleteRole(Role $role): bool
    {
        if ($this->isProtected($role)) {
            return false;
        }

        return $this->roleRepository->delete($role);
    }

    /**
     * Check if a role is protected from deletion/modification.
     */
    public function isProtected(Model|Role $model): bool
    {
        return $model->isProtected();
    }
}
