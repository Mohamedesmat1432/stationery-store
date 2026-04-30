<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return User::with('roles')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function restore(User $user): bool
    {
        return $user->restore();
    }

    public function forceDelete(User $user): bool
    {
        return $user->forceDelete();
    }

    public function bulkDelete(array $ids): bool
    {
        return User::whereIn('id', $ids)->delete() > 0;
    }

    public function bulkRestore(array $ids): bool
    {
        return User::onlyTrashed()->whereIn('id', $ids)->restore() > 0;
    }

    public function bulkForceDelete(array $ids): bool
    {
        return User::onlyTrashed()->whereIn('id', $ids)->forceDelete() > 0;
    }

    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }
}
