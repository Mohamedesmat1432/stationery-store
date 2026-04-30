<?php

namespace App\Services\CRM;

use App\Data\CRM\CustomerGroupData;
use App\Models\CustomerGroup;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerGroupService
{
    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository
    ) {
    }

    public function getCustomerGroupsPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(CustomerGroup::class)
            ->withCount('customers')
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ])
            ->defaultSort('sort_order', '-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createCustomerGroup(CustomerGroupData $data): CustomerGroup
    {
        return $this->customerGroupRepository->create($data->toArray());
    }

    public function updateCustomerGroup(CustomerGroup $group, CustomerGroupData $data): CustomerGroup
    {
        return $this->customerGroupRepository->update($group, $data->toArray());
    }

    public function deleteCustomerGroup(CustomerGroup $group): bool
    {
        return $this->customerGroupRepository->delete($group);
    }

    public function restoreCustomerGroup(CustomerGroup $group): bool
    {
        return $this->customerGroupRepository->restore($group);
    }

    public function forceDeleteCustomerGroup(CustomerGroup $group): bool
    {
        return $this->customerGroupRepository->forceDelete($group);
    }

    public function bulkDeleteCustomerGroups(array $ids): bool
    {
        return $this->customerGroupRepository->bulkDelete($ids);
    }

    public function bulkRestoreCustomerGroups(array $ids): bool
    {
        return $this->customerGroupRepository->bulkRestore($ids);
    }

    public function bulkForceDeleteCustomerGroups(array $ids): bool
    {
        return $this->customerGroupRepository->bulkForceDelete($ids);
    }

    public function getAllActive(): Collection
    {
        return $this->customerGroupRepository->allActive();
    }
}
