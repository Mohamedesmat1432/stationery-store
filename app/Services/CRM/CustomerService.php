<?php

namespace App\Services\CRM;

use App\Data\CRM\CustomerData;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\Concerns\HandlesBulkOperations;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerService
{
    use HandlesBulkOperations;

    public function __construct(
        protected CustomerRepositoryInterface $customerRepository
    ) {}

    public function getCustomersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage);
    }

    public function createCustomer(CustomerData $data): Customer
    {
        /** @var Customer $customer */
        $customer = $this->customerRepository->create($data->toArray());

        return $customer;
    }

    public function updateCustomer(Customer $customer, CustomerData $data): Customer
    {
        /** @var Customer $updatedCustomer */
        $updatedCustomer = $this->customerRepository->update($customer, $data->toArray());

        return $updatedCustomer;
    }

    public function deleteCustomer(Customer $customer): bool
    {
        return $this->customerRepository->delete($customer);
    }

    public function restoreCustomer(Customer $customer): bool
    {
        return $this->customerRepository->restore($customer);
    }

    public function forceDeleteCustomer(Customer $customer): bool
    {
        return $this->customerRepository->forceDelete($customer);
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
        Excel::import(new CustomersImport, $file);
    }
}
