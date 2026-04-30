<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    protected function getModelClass(): string
    {
        return Customer::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Customer::with(['user', 'group'])->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): Customer
    {
        return Customer::with(['user', 'group', 'addresses'])->findOrFail($id);
    }
}
