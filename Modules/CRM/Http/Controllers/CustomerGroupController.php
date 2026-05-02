<?php

namespace Modules\CRM\Http\Controllers;

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

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', CustomerGroup::class);

        return Inertia::render('Admin/CustomerGroups/Index', [
            'groups' => CustomerGroupData::collect($this->customerGroupService->getCustomerGroupsPaginated()),
            'filters' => $request->only(['filter']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', CustomerGroup::class);

        return Inertia::render('Admin/CustomerGroups/Create');
    }

    public function store(CustomerGroupData $data): RedirectResponse
    {
        Gate::authorize('create', CustomerGroup::class);

        $this->customerGroupService->createCustomerGroup($data);

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer group created successfully.'));
    }

    public function edit(CustomerGroup $customerGroup): Response
    {
        Gate::authorize('update', $customerGroup);

        return Inertia::render('Admin/CustomerGroups/Edit', [
            'group' => CustomerGroupData::from($customerGroup),
        ]);
    }

    public function update(CustomerGroup $customerGroup, CustomerGroupData $data): RedirectResponse
    {
        Gate::authorize('update', $customerGroup);

        $this->customerGroupService->updateCustomerGroup($customerGroup, $data);

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer group updated successfully.'));
    }

    public function destroy(CustomerGroup $customerGroup): RedirectResponse
    {
        Gate::authorize('delete', $customerGroup);

        $this->customerGroupService->deleteCustomerGroup($customerGroup);

        return to_route('admin.customer-groups.index')
            ->with('success', __('Customer group deleted successfully.'));
    }

    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, CustomerGroup::class, 'customerGroupService', 'restoreCustomerGroup');
    }

    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, CustomerGroup::class, 'customerGroupService', 'forceDeleteCustomerGroup');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, CustomerGroup::class, 'customerGroupService');
    }

    public function export(ExportCustomerGroupsData $data): BinaryFileResponse
    {
        Gate::authorize('export', CustomerGroup::class);

        return $this->customerGroupService->exportCustomerGroups($data->columns, $data->format);
    }

    public function import(ImportCustomerGroupsData $data): RedirectResponse
    {
        Gate::authorize('import', CustomerGroup::class);

        $this->customerGroupService->importCustomerGroups($data->file);

        return to_route('admin.customer-groups.index')->with('success', __('Customer groups imported successfully.'));
    }
}
