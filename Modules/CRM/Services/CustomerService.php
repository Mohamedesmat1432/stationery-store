<?php

namespace Modules\CRM\Services;

use App\Models\Customer;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Modules\CRM\Data\CustomerData;
use Modules\CRM\Exports\CustomersExport;
use Modules\CRM\Imports\CustomersImport;
use Modules\CRM\Repositories\Contracts\CustomerRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
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
        $updateData = collect($data->toArray())
            ->only([
                'user_id', 'phone', 'birth_date', 'gender', 'tax_number',
                'company_name', 'customer_group_id', 'metadata',
            ])
            ->toArray();

        $customer = $this->customerRepository->update($customer, $updateData);

        ResourceChanged::dispatch(Customer::class, 'updated', [$customer->id]);

        return $customer;
    }

    /**
     * Delete a customer.
     */
    public function deleteCustomer(Customer $customer): bool
    {
        $result = $this->customerRepository->delete($customer);

        if ($result) {
            ResourceChanged::dispatch(Customer::class, 'deleted', [$customer->id]);
        }

        return $result;
    }

    /**
     * Restore a soft-deleted customer.
     */
    public function restoreCustomer(Customer $customer): bool
    {
        $result = $this->customerRepository->restore($customer);

        if ($result) {
            ResourceChanged::dispatch(Customer::class, 'restored', [$customer->id]);
        }

        return $result;
    }

    /**
     * Permanently delete a customer.
     */
    public function forceDeleteCustomer(Customer $customer): bool
    {
        $result = $this->customerRepository->forceDelete($customer);

        if ($result) {
            ResourceChanged::dispatch(Customer::class, 'force_deleted', [$customer->id]);
        }

        return $result;
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
