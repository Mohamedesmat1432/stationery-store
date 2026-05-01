<?php

namespace App\Http\Controllers\Admin;

use App\Data\AccessControl\ExportUsersData;
use App\Data\AccessControl\ImportUsersData;
use App\Data\AccessControl\UserData;
use App\Http\Controllers\Admin\Traits\HandlesBulkActions;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AccessControl\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $users = $this->userService->getUsersPaginated();

        return Inertia::render('Admin/Users/Index', [
            'users' => UserData::collect($users),
            'filters' => $request->only(['filter']),
            'available_roles' => Role::pluck('name'),
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'available_roles' => Role::pluck('name'),
        ]);
    }

    public function store(UserData $data): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $this->userService->createUser($data);

        return to_route('admin.users.index')->with('success', __('User created successfully.'));
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Admin/Users/Edit', [
            'user' => UserData::fromModel($user)->toArray(),
            'available_roles' => Role::pluck('name')->toArray(),
        ]);
    }

    public function update(UserData $data, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $this->userService->updateUser($user, $data);

        return to_route('admin.users.index')->with('success', __('User updated successfully.'));
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $this->userService->deleteUser($user);

        return to_route('admin.users.index')->with('success', __('User deleted successfully.'));
    }

    public function restore($id): RedirectResponse
    {
        return $this->performRestore($id, User::class, 'userService', 'restoreUser');
    }

    public function forceDelete($id): RedirectResponse
    {
        return $this->performForceDelete($id, User::class, 'userService', 'forceDeleteUser');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, User::class, 'userService');
    }

    public function export(ExportUsersData $data): BinaryFileResponse
    {
        Gate::authorize('export', User::class);

        return $this->userService->exportUsers($data->columns, $data->format);
    }

    public function import(ImportUsersData $data): RedirectResponse
    {
        Gate::authorize('import', User::class);

        $this->userService->importUsers($data->file);

        return to_route('admin.users.index')->with('success', __('Users imported successfully.'));
    }
}
