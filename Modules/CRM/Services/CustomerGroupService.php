<?php

namespace Modules\CRM\Services;

use App\Models\CustomerGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel;
use Modules\CRM\Data\CustomerGroupData;
use Modules\CRM\Exports\CustomerGroupsExport;
use Modules\CRM\Imports\CustomerGroupsImport;
use Modules\CRM\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function getAllActive(): array
    {
        return CRMCacheService::getActiveCustomerGroups();
    }

    public function exportCustomerGroups(array $columns, string $formatKey): BinaryFileResponse
    {
        $format = $formatKey === 'csv' ? Excel::CSV : Excel::XLSX;
        $extension = $formatKey === 'csv' ? 'csv' : 'xlsx';

        return \Maatwebsite\Excel\Facades\Excel::download(
            new CustomerGroupsExport($this->customerGroupRepository->getExportQuery(), $columns),
            'customer-groups.'.$extension,
            $format
        );
    }

    public function importCustomerGroups(UploadedFile $file): void
    {
        \Maatwebsite\Excel\Facades\Excel::import(new CustomerGroupsImport, $file);
    }
}
