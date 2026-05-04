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
use Modules\Shared\Events\ResourceChanged;
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

    public function createCustomerGroup(CustomerGroupData $data): CustomerGroup
    {
        $group = $this->customerGroupRepository->create($data->toArray());

        ResourceChanged::dispatch(CustomerGroup::class, 'created', [$group->id]);

        return $group;
    }

    /**
     * Update an existing customer group.
     */
    public function updateCustomerGroup(CustomerGroup $group, CustomerGroupData $data): CustomerGroup
    {
        $updateData = collect($data->toArray())
            ->only(['name', 'slug', 'description', 'discount_percentage', 'is_active', 'sort_order'])
            ->toArray();

        $group = $this->customerGroupRepository->update($group, $updateData);

        ResourceChanged::dispatch(CustomerGroup::class, 'updated', [$group->id]);

        return $group;
    }

    /**
     * Delete a customer group if not protected.
     */
    public function deleteCustomerGroup(CustomerGroup $group): bool
    {
        if ($this->isProtected($group)) {
            return false;
        }

        $result = $this->customerGroupRepository->delete($group);

        if ($result) {
            ResourceChanged::dispatch(CustomerGroup::class, 'deleted', [$group->id]);
        }

        return $result;
    }

    /**
     * Restore a soft-deleted customer group.
     */
    public function restoreCustomerGroup(CustomerGroup $group): bool
    {
        $result = $this->customerGroupRepository->restore($group);

        if ($result) {
            ResourceChanged::dispatch(CustomerGroup::class, 'restored', [$group->id]);
        }

        return $result;
    }

    /**
     * Permanently delete a customer group.
     */
    public function forceDeleteCustomerGroup(CustomerGroup $group): bool
    {
        if ($this->isProtected($group)) {
            return false;
        }

        $result = $this->customerGroupRepository->forceDelete($group);

        if ($result) {
            ResourceChanged::dispatch(CustomerGroup::class, 'force_deleted', [$group->id]);
        }

        return $result;
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

        ResourceChanged::dispatch(CustomerGroup::class, 'imported');
    }
}
