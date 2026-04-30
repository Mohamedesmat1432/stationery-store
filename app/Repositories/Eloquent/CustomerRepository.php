<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::with(['user', 'group'])->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): Customer
    {
        return Customer::with(['user', 'group', 'addresses'])->findOrFail($id);
    }

    public function create(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }

    public function delete(Customer $customer): bool
    {
        return $customer->delete();
    }

    public function restore(Customer $customer): bool
    {
        return $customer->restore();
    }

    public function forceDelete(Customer $customer): bool
    {
        return $customer->forceDelete();
    }

    public function bulkDelete(array $ids): bool
    {
        return Customer::whereIn('id', $ids)
            ->get()
            ->each(fn (Customer $customer) => $customer->delete())
            ->isNotEmpty();
    }

    public function bulkRestore(array $ids): bool
    {
        return Customer::onlyTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (Customer $customer) => $customer->restore())
            ->isNotEmpty();
    }

    public function bulkForceDelete(array $ids): bool
    {
        return Customer::onlyTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (Customer $customer) => $customer->forceDelete())
            ->isNotEmpty();
    }
}
