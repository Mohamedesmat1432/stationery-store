<?php

namespace Modules\Identity\Repositories\Eloquent;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Identity\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    protected function getModelClass(): string
    {
        return Role::class;
    }

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

    public function findById(string $id): Role
    {
        return Role::with('permissions')->findOrFail($id);
    }

    public function syncPermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
