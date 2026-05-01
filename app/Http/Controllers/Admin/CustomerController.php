<?php

namespace App\Http\Controllers\Admin;

use App\Data\CRM\CustomerData;
use App\Data\CRM\CustomerGroupData;
use App\Data\CRM\ExportCustomersData;
use App\Data\CRM\ImportCustomersData;
use App\Http\Controllers\Admin\Traits\HandlesBulkActions;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\AccessControl\UserService;
use App\Services\CRM\CustomerGroupService;
use App\Services\CRM\CustomerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected CustomerService $customerService,
        protected CustomerGroupService $customerGroupService,
        protected UserService $userService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Customer::class);

        return Inertia::render('Admin/Customers/Index', [
            'customers' => CustomerData::collect($this->customerService->getCustomersPaginated()),
            'filters' => $request->only(['filter']),
            'available_groups' => CustomerGroupData::collect($this->customerGroupService->getAllActive()),
            'available_users' => $this->userService->getAvailableForCustomer(),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Customer::class);

        return Inertia::render('Admin/Customers/Create', [
            'available_groups' => CustomerGroupData::collect($this->customerGroupService->getAllActive()),
            'available_users' => $this->userService->getAvailableForCustomer(),
        ]);
    }

    public function store(CustomerData $data): RedirectResponse
    {
        Gate::authorize('create', Customer::class);

        $this->customerService->createCustomer($data);

        return to_route('admin.customers.index')->with('success', __('Customer created successfully.'));
    }

    public function edit(Customer $customer): Response
    {
        Gate::authorize('update', $customer);

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => CustomerData::from($customer),
            'available_groups' => CustomerGroupData::collect($this->customerGroupService->getAllActive()),
            'available_users' => $this->userService->getAvailableForCustomer($customer->user_id),
        ]);
    }

    public function update(Customer $customer, CustomerData $data): RedirectResponse
    {
        Gate::authorize('update', $customer);

        $this->customerService->updateCustomer($customer, $data);

        return to_route('admin.customers.index')->with('success', __('Customer updated successfully.'));
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $this->customerService->deleteCustomer($customer);

        return to_route('admin.customers.index')->with('success', __('Customer deleted successfully.'));
    }

    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, Customer::class, 'customerService', 'restoreCustomer');
    }

    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, Customer::class, 'customerService', 'forceDeleteCustomer');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Customer::class, 'customerService');
    }

    public function export(ExportCustomersData $data): BinaryFileResponse
    {
        Gate::authorize('export', Customer::class);

        return $this->customerService->exportCustomers($data->columns, $data->format);
    }

    public function import(ImportCustomersData $data): RedirectResponse
    {
        Gate::authorize('import', Customer::class);

        $this->customerService->importCustomers($data->file);

        return to_route('admin.customers.index')->with('success', __('Customers imported successfully.'));
    }
}
