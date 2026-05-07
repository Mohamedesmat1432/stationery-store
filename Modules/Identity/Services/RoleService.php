<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Identity\Data\RoleData;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\HandlesResourceOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Modules\Shared\Services\Logging\ModuleLogger;

class RoleService
{
    use HandlesBulkOperations, HandlesResourceOperations, ModuleLogger, ProtectsSystemResources {
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
        try {
            return DB::transaction(function () use ($data) {
                $role = $this->roleRepository->create(['name' => $data->name]);
                $this->roleRepository->syncPermissions($role, $data->permissions);

                ResourceChanged::dispatch(Role::class, 'created', [$role->id]);

                return $role;
            });
        } catch (\Throwable $e) {
            $this->logError('Failed to create role', ['name' => $data->name], $e);
            throw $e;
        }
    }

    /**
     * Update an existing role.
     */
    public function updateRole(Role $role, RoleData $data): Role
    {
        try {
            return DB::transaction(function () use ($role, $data) {
                $role = $this->roleRepository->update($role, ['name' => $data->name]);
                $this->roleRepository->syncPermissions($role, $data->permissions);

                ResourceChanged::dispatch(Role::class, 'updated', [$role->id]);

                return $role;
            });
        } catch (\Throwable $e) {
            $this->logError('Failed to update role', ['id' => $role->id, 'name' => $role->name], $e);
            throw $e;
        }
    }

    public function deleteRole(Role $role): bool
    {
        return $this->performDelete($role);
    }

    /**
     * Check if a role is protected from deletion/modification.
     */
    public function isProtected(Model|Role $model): bool
    {
        return $model->isProtected();
    }
}
