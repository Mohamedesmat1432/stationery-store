<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Identity\Data\RoleData;
use Modules\Identity\Services\IdentityCacheService;
use Modules\Identity\Services\RoleService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;

class RoleController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Role::class);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $this->roleService->getRolesPaginated($request->all()),
            'filters' => $request->only(['filter']),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Role::class);

        return Inertia::render('Admin/Roles/Create', [
            'available_permissions' => Inertia::defer(fn () => IdentityCacheService::getAvailablePermissions()),
        ]);
    }

    public function store(RoleData $data): RedirectResponse
    {
        Gate::authorize('create', Role::class);

        $this->roleService->createRole($data);

        return to_route('admin.roles.index')->with('success', __('Role created successfully.'));
    }

    public function show(Role $role): RedirectResponse
    {
        Gate::authorize('view', $role);

        return to_route('admin.roles.edit', $role);
    }

    public function edit(Role $role): Response
    {
        Gate::authorize('update', $role);

        return Inertia::render('Admin/Roles/Edit', [
            'role' => RoleData::fromRole($role->loadMissing('permissions')),
            'available_permissions' => Inertia::defer(fn () => IdentityCacheService::getAvailablePermissions()),
        ]);
    }

    public function update(RoleData $data, Role $role): RedirectResponse
    {
        Gate::authorize('update', $role);

        $this->roleService->updateRole($role, $data);

        return to_route('admin.roles.index')->with('success', __('Role updated successfully.'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        $deleted = $this->roleService->deleteRole($role);

        if (! $deleted) {
            return back()->with('error', __('This role is protected and cannot be deleted.'));
        }

        return to_route('admin.roles.index')->with('success', __('Role deleted successfully.'));
    }

    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, Role::class, 'roleService');
    }
}
