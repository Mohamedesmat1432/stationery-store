<?php

namespace Modules\CRM\Services;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Data\CustomerData;
use Modules\CRM\Exports\CustomersExport;
use Modules\CRM\Imports\CustomersImport;
use Modules\CRM\Repositories\Contracts\CustomerRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\HandlesResourceOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Modules\Shared\Services\Logging\ModuleLogger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerService
{
    use HandlesBulkOperations, HandlesResourceOperations, ModuleLogger, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    protected function getRepository(): CustomerRepositoryInterface
    {
        return $this->customerRepository;
    }

    /**
     * Get paginated customers.
     */
    public function getCustomersPaginated(array $params = [], int $perPage = 15): array
    {
        return CRMCacheService::rememberCustomers(
            $params,
            $perPage,
            fn () => $this->customerRepository->paginate($perPage, $params)
        );
    }

    public function createCustomer(CustomerData $data): Customer
    {
        $customer = $this->customerRepository->create($data->toArray());

        ResourceChanged::dispatch(Customer::class, 'created', [$customer->id]);

        return $customer;
    }

    /**
     * Update an existing customer.
     */
    public function updateCustomer(Customer $customer, CustomerData $data): Customer
    {
        $updateData = $data->except('is_protected', 'total_spent', 'orders_count', 'age', 'name', 'email', 'deleted_at', 'group_name')->toArray();

        $customer = $this->customerRepository->update($customer, $updateData);

        ResourceChanged::dispatch(Customer::class, 'updated', [$customer->id]);

        return $customer;
    }

    /**
     * Delete a customer.
     */
    public function deleteCustomer(Customer $customer): bool
    {
        return $this->performDelete($customer);
    }

    /**
     * Restore a soft-deleted customer.
     */
    public function restoreCustomer(Customer $customer): bool
    {
        return $this->performRestore($customer);
    }

    /**
     * Permanently delete a customer.
     */
    public function forceDeleteCustomer(Customer $customer): bool
    {
        return $this->performForceDelete($customer);
    }

    /**
     * Check if a customer is protected from deletion/modification.
     */
    public function isProtected(Model|Customer $model): bool
    {
        return $model->isProtected(Auth::user());
    }

    public function exportCustomers(array $columns, string $formatKey): BinaryFileResponse
    {
        $format = $formatKey === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $extension = $formatKey === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(
            new CustomersExport($this->customerRepository->getExportQuery(), $columns),
            'customers.'.$extension,
            $format
        );
    }

    public function importCustomers(UploadedFile $file): void
    {
        Customer::withoutEvents(fn () => Excel::import(new CustomersImport, $file));

        ResourceChanged::dispatch(Customer::class, 'imported');
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }
}
