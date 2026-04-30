<?php

namespace App\Http\Controllers\Admin;

use App\Data\AccessControl\RoleData;
use App\Enums\PermissionName;
use App\Http\Controllers\Admin\Traits\HandlesBulkActions;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Services\AccessControl\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RoleController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected RoleService $roleService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', Role::class);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => RoleData::collect($this->roleService->getRolesPaginated()),
            'filters' => $request->only(['filter']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Role::class);

        return Inertia::render('Admin/Roles/Create', [
            'available_permissions' => PermissionName::values(),
        ]);
    }

    public function store(RoleData $data)
    {
        Gate::authorize('create', Role::class);

        $this->roleService->createRole($data);

        return to_route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        Gate::authorize('update', $role);

        return Inertia::render('Admin/Roles/Edit', [
            'role' => RoleData::fromModel($role),
            'available_permissions' => PermissionName::values(),
        ]);
    }

    public function update(RoleData $data, Role $role)
    {
        Gate::authorize('update', $role);

        $this->roleService->updateRole($role, $data);

        return to_route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        Gate::authorize('delete', $role);

        $this->roleService->deleteRole($role);

        return to_route('admin.roles.index')->with('success', 'Role deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        return $this->performBulkAction($request, Role::class, 'roleService', 'Roles');
    }
}
