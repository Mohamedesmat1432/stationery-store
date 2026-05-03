<?php

namespace Modules\CRM\Services;

use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Data\CustomerData;
use Modules\CRM\Exports\CustomersExport;
use Modules\CRM\Imports\CustomersImport;
use Modules\CRM\Repositories\Contracts\CustomerRepositoryInterface;
use Modules\Shared\Events\BulkOperationCompleted;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerService
{
    use HandlesBulkOperations;

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
            fn () => $this->customerRepository->paginate($perPage)
        );
    }

    /**
     * Create a new customer.
     */
    public function createCustomer(CustomerData $data): Customer
    {
        return DB::transaction(function () use ($data) {
            return $this->customerRepository->create($data->toArray());
        });
    }

    /**
     * Update an existing customer.
     */
    public function updateCustomer(Customer $customer, CustomerData $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            return $this->customerRepository->update($customer, $data->toArray());
        });
    }

    /**
     * Delete a customer.
     */
    public function deleteCustomer(Customer $customer): bool
    {
        return DB::transaction(fn () => $this->customerRepository->delete($customer));
    }

    /**
     * Restore a soft-deleted customer.
     */
    public function restoreCustomer(Customer $customer): bool
    {
        return DB::transaction(fn () => $this->customerRepository->restore($customer));
    }

    /**
     * Permanently delete a customer.
     */
    public function forceDeleteCustomer(Customer $customer): bool
    {
        return DB::transaction(fn () => $this->customerRepository->forceDelete($customer));
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

        event(new BulkOperationCompleted(Customer::class, 'import'));
    }

    protected function getModelClass(): string
    {
        return Customer::class;
    }
}
