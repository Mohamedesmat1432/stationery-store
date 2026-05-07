<?php

namespace Modules\CRM\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\CRM\Data\CustomerGroupData;
use Modules\CRM\Data\ExportCustomerGroupsData;
use Modules\CRM\Data\ImportCustomerGroupsData;
use Modules\CRM\Services\CustomerGroupService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerGroupController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected CustomerGroupService $customerGroupService
    ) {}

    /**
     * Display a listing of customer groups.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', CustomerGroup::class);

        return Inertia::render('Admin/CustomerGroups/Index', [
            'groups' => Inertia::defer(fn () => $this->customerGroupService->getCustomerGroupsPaginated($request->all())),
            'filters' => $request->only(['filter']),
        ]);
    }

    /**
     * Show the form for creating a new customer group.
     */
    public function create(): Response
    {
        Gate::authorize('create', CustomerGroup::class);

        return Inertia::render('Admin/CustomerGroups/Create');
    }

    /**
     * Store a newly created customer group in storage.
     */
    public function store(CustomerGroupData $data): RedirectResponse
    {
        Gate::authorize('create', CustomerGroup::class);

        $this->customerGroupService->createCustomerGroup($data);

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer group created successfully.'));
    }

    /**
     * Display the specified customer group.
     */
    public function show(CustomerGroup $customerGroup): RedirectResponse
    {
        Gate::authorize('view', $customerGroup);

        return to_route('admin.customer-groups.edit', $customerGroup);
    }

    /**
     * Show the form for editing the specified customer group.
     */
    public function edit(CustomerGroup $customerGroup): Response
    {
        Gate::authorize('update', $customerGroup);

        return Inertia::render('Admin/CustomerGroups/Edit', [
            'group' => CustomerGroupData::fromCustomerGroup($customerGroup->loadCount('customers')),
        ]);
    }

    /**
     * Update the specified customer group in storage.
     */
    public function update(CustomerGroup $customerGroup, CustomerGroupData $data): RedirectResponse
    {
        Gate::authorize('update', $customerGroup);

        $this->customerGroupService->updateCustomerGroup($customerGroup, $data);

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer group updated successfully.'));
    }

    /**
     * Remove the specified customer group from storage.
     */
    public function destroy(CustomerGroup $customerGroup): RedirectResponse
    {
        Gate::authorize('delete', $customerGroup);

        $deleted = $this->customerGroupService->deleteCustomerGroup($customerGroup);

        if (! $deleted) {
            return back()->with('error', __('This customer group is protected and cannot be deleted.'));
        }

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer group deleted successfully.'));
    }

    /**
     * Restore a soft-deleted customer group.
     *
     * @param  string  $id
     */
    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, CustomerGroup::class, 'customerGroupService', 'restoreCustomerGroup');
    }

    /**
     * Permanently delete a customer group.
     *
     * @param  string  $id
     */
    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, CustomerGroup::class, 'customerGroupService', 'forceDeleteCustomerGroup');
    }

    /**
     * Handle bulk actions for customer groups.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, CustomerGroup::class, 'customerGroupService');
    }

    /**
     * Export customer groups.
     */
    public function export(Request $request, ExportCustomerGroupsData $data): BinaryFileResponse
    {
        Gate::authorize('export', CustomerGroup::class);

        return $this->customerGroupService->exportCustomerGroups($data->columns, $data->format, $request->all());
    }

    /**
     * Import customer groups.
     */
    public function import(ImportCustomerGroupsData $data): RedirectResponse
    {
        Gate::authorize('import', CustomerGroup::class);

        $this->customerGroupService->importCustomerGroups($data->file);

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer groups imported successfully.'));
    }
}
