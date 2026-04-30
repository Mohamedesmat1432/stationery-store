<?php

namespace App\Repositories\Contracts;

use App\Models\CustomerGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CustomerGroupRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): CustomerGroup;

    public function create(array $data): CustomerGroup;

    public function update(CustomerGroup $group, array $data): CustomerGroup;

    public function delete(CustomerGroup $group): bool;

    public function restore(CustomerGroup $group): bool;

    public function forceDelete(CustomerGroup $group): bool;

    public function bulkDelete(array $ids): bool;

    public function bulkRestore(array $ids): bool;

    public function bulkForceDelete(array $ids): bool;

    public function allActive(): Collection;
}
