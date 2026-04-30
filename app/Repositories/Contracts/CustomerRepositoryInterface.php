<?php

namespace App\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CustomerRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(string $id): Customer;

    public function create(array $data): Customer;

    public function update(Customer $customer, array $data): Customer;

    public function delete(Customer $customer): bool;

    public function restore(Customer $customer): bool;

    public function forceDelete(Customer $customer): bool;

    public function bulkDelete(array $ids): bool;

    public function bulkRestore(array $ids): bool;

    public function bulkForceDelete(array $ids): bool;
}
