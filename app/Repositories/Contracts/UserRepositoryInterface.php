<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): User;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function delete(User $user): bool;

    public function restore(User $user): bool;

    public function forceDelete(User $user): bool;

    public function bulkDelete(array $ids): bool;

    public function bulkRestore(array $ids): bool;

    public function bulkForceDelete(array $ids): bool;

    public function syncRoles(User $user, array $roles): void;
}
