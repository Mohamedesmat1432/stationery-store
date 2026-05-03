<?php

namespace Modules\CRM\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CRM\Data\CustomerData;
use Modules\CRM\Data\ExportCustomersData;
use Modules\CRM\Data\ImportCustomersData;
use Modules\CRM\Services\CustomerGroupService;
use Modules\CRM\Services\CustomerService;
use Modules\Identity\Services\UserService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected CustomerService $customerService,
        protected CustomerGroupService $customerGroupService,
        protected UserService $userService
    ) {}

    /**
     * Display a listing of customers.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Customer::class);

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $this->customerService->getCustomersPaginated($request->all()),
            'filters' => $request->only(['filter']),
            'available_groups' => Inertia::defer(fn () => $this->customerGroupService->getAllActive()),
            'available_users' => Inertia::defer(fn () => $this->userService->getAvailableForCustomer()),
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): Response
    {
        Gate::authorize('create', Customer::class);

        return Inertia::render('Admin/Customers/Create', [
            'available_groups' => Inertia::defer(fn () => $this->customerGroupService->getAllActive()),
            'available_users' => Inertia::defer(fn () => $this->userService->getAvailableForCustomer()),
        ]);
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(CustomerData $data): RedirectResponse
    {
        Gate::authorize('create', Customer::class);

        $this->customerService->createCustomer($data);

        return to_route('admin.customers.index')->with('success', __('Customer created successfully.'));
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): RedirectResponse
    {
        Gate::authorize('view', $customer);

        return to_route('admin.customers.edit', $customer);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        Gate::authorize('update', $customer);

        return Inertia::render('Admin/Customers/Edit', [
            'customer' => CustomerData::fromCustomer($customer->loadMissing(['user', 'group'])),
            'available_groups' => Inertia::defer(fn () => $this->customerGroupService->getAllActive()),
            'available_users' => Inertia::defer(fn () => $this->userService->getAvailableForCustomer($customer->user_id)),
        ]);
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Customer $customer, CustomerData $data): RedirectResponse
    {
        Gate::authorize('update', $customer);

        $this->customerService->updateCustomer($customer, $data);

        return to_route('admin.customers.index')->with('success', __('Customer updated successfully.'));
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);

        $this->customerService->deleteCustomer($customer);

        return to_route('admin.customers.index')->with('success', __('Customer deleted successfully.'));
    }

    /**
     * Restore a soft-deleted customer.
     *
     * @param  string  $id
     */
    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, Customer::class, 'customerService', 'restoreCustomer');
    }

    /**
     * Permanently delete a customer.
     *
     * @param  string  $id
     */
    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, Customer::class, 'customerService', 'forceDeleteCustomer');
    }

    /**
     * Handle bulk actions for customers.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Customer::class, 'customerService');
    }

    /**
     * Export customers.
     */
    public function export(ExportCustomersData $data): BinaryFileResponse
    {
        Gate::authorize('export', Customer::class);

        return $this->customerService->exportCustomers($data->columns, $data->format);
    }

    /**
     * Import customers.
     */
    public function import(ImportCustomersData $data): RedirectResponse
    {
        Gate::authorize('import', Customer::class);

        $this->customerService->importCustomers($data->file);

        return to_route('admin.customers.index')->with('success', __('Customers imported successfully.'));
    }
}
