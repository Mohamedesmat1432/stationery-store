<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    protected function getModelClass(): string
    {
        return User::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('roles')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }
}
