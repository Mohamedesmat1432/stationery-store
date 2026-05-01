<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->with('roles')
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->role($value);
                }),
                AllowedFilter::trashed('trash'),
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    public function getExportQuery(): Builder
    {
        return User::query()->with('roles');
    }
}
