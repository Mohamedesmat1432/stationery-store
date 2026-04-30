<?php

namespace App\Repositories\Eloquent;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class RoleRepository implements RoleRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Role::with('permissions')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function delete(Role $role): bool
    {
        return $role->delete();
    }

    public function bulkDelete(array $ids): bool
    {
        return DB::transaction(fn () => Role::whereIn('id', $ids)
            ->get()
            ->each(fn (Role $role) => $role->delete())
            ->isNotEmpty()
        );
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
