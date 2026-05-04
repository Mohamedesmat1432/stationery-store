<?php

namespace Modules\CRM\Services;

use App\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Excel;
use Modules\CRM\Data\CustomerGroupData;
use Modules\CRM\Exports\CustomerGroupsExport;
use Modules\CRM\Imports\CustomerGroupsImport;
use Modules\CRM\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Modules\Shared\Events\BulkOperationCompleted;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerGroupService
{
    use HandlesBulkOperations, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected CustomerGroupRepositoryInterface $customerGroupRepository
    ) {}

    protected function getRepository(): CustomerGroupRepositoryInterface
    {
        return $this->customerGroupRepository;
    }

    /**
     * Check if a customer group is protected.
     */
    public function isProtected(Model|CustomerGroup $model): bool
    {
        return $model->isProtected();
    }

    /**
     * Get the model class for this service.
     */
    protected function getModelClass(): string
    {
        return CustomerGroup::class;
    }

    /**
     * Get paginated customer groups.
     */
    public function getCustomerGroupsPaginated(array $params = [], int $perPage = 15): array
    {
        return CRMCacheService::rememberCustomerGroups(
            $params,
            $perPage,
            fn () => $this->customerGroupRepository->paginate($perPage)
        );
    }

    /**
     * Create a new customer group.
     */
    public function createCustomerGroup(CustomerGroupData $data): CustomerGroup
    {
        return $this->customerGroupRepository->create($data->toArray());
    }

    /**
     * Update an existing customer group.
     */
    public function updateCustomerGroup(CustomerGroup $group, CustomerGroupData $data): CustomerGroup
    {
        $updateData = collect($data->toArray())
            ->only(['name', 'slug', 'description', 'discount_percentage', 'is_active', 'sort_order'])
            ->toArray();

        return $this->customerGroupRepository->update($group, $updateData);
    }

    /**
     * Delete a customer group if not protected.
     */
    public function deleteCustomerGroup(CustomerGroup $group): bool
    {
        if ($this->isProtected($group)) {
            return false;
        }

        return $this->customerGroupRepository->delete($group);
    }

    /**
     * Restore a soft-deleted customer group.
     */
    public function restoreCustomerGroup(CustomerGroup $group): bool
    {
        return $this->customerGroupRepository->restore($group);
    }

    /**
     * Permanently delete a customer group.
     */
    public function forceDeleteCustomerGroup(CustomerGroup $group): bool
    {
        if ($this->isProtected($group)) {
            return false;
        }

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

        return Excel::download(
            new CustomerGroupsExport($this->customerGroupRepository->getExportQuery(), $columns),
            'customer-groups.'.$extension,
            $format
        );
    }

    public function importCustomerGroups(UploadedFile $file): void
    {
        CustomerGroup::withoutEvents(fn () => Excel::import(new CustomerGroupsImport, $file));

        event(new BulkOperationCompleted(CustomerGroup::class, 'import'));
    }
}
