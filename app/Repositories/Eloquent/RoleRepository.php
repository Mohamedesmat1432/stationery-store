<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected function getModelClass(): string
    {
        return Role::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Role::with('permissions')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
