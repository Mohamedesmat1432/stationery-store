<?php

namespace App\Services\CRM;

use App\Data\CRM\CustomerGroupData;
use App\Models\CustomerGroup;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use App\Services\Concerns\HandlesBulkOperations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerGroupService
{
    use HandlesBulkOperations;

    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository
    ) {}

    public function getCustomerGroupsPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerGroupRepository->paginate($perPage);
    }

    public function createCustomerGroup(CustomerGroupData $data): CustomerGroup
    {
        /** @var CustomerGroup $group */
        $group = $this->customerGroupRepository->create($data->toArray());

        return $group;
    }

    public function updateCustomerGroup(CustomerGroup $group, CustomerGroupData $data): CustomerGroup
    {
        /** @var CustomerGroup $updatedGroup */
        $updatedGroup = $this->customerGroupRepository->update($group, $data->toArray());

        return $updatedGroup;
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

    public function getAllActive(): Collection
    {
        return $this->customerGroupRepository->allActive();
    }
}
