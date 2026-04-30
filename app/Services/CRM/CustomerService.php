<?php

namespace App\Services\CRM;

use App\Data\CRM\CustomerData;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerService
{
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    public function getCustomersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Customer::class)
            ->with(['user', 'group'])
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('group', 'customer_group_id'),
                AllowedFilter::callback('trash', function ($query, $value) {
                    if ($value === 'only') {
                        $query->onlyTrashed();
                    } elseif ($value === 'with') {
                        $query->withTrashed();
                    }
                }),
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createCustomer(CustomerData $data): Customer
    {
        return $this->customerRepository->create($data->toArray());
    }

    public function updateCustomer(Customer $customer, CustomerData $data): Customer
    {
        return $this->customerRepository->update($customer, $data->toArray());
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return $this->customerRepository->delete($customer);
    }

    public function bulkDeleteCustomers(array $ids): bool
    {
        return $this->customerRepository->bulkDelete($ids);
    }

    public function restoreCustomer(Customer $customer): bool
    {
        return $this->customerRepository->restore($customer);
    }

    public function forceDeleteCustomer(Customer $customer): bool
    {
        return $this->customerRepository->forceDelete($customer);
    }

    public function bulkRestoreCustomers(array $ids): bool
    {
        return $this->customerRepository->bulkRestore($ids);
    }

    public function bulkForceDeleteCustomers(array $ids): bool
    {
        return $this->customerRepository->bulkForceDelete($ids);
    }
}
