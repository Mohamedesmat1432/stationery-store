<?php

namespace App\Http\Controllers\Admin;

use App\Data\AccessControl\UserData;
use App\Http\Controllers\Admin\Traits\HandlesBulkActions;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\AccessControl\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class UserController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected UserService $userService
    ) {}

    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);

        $users = $this->userService->getUsersPaginated();

        return Inertia::render('Admin/Users/Index', [
            'users' => UserData::collect($users),
            'filters' => $request->only(['filter']),
            'available_roles' => Role::pluck('name'),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'available_roles' => Role::pluck('name'),
        ]);
    }

    public function store(UserData $data)
    {
        Gate::authorize('create', User::class);

        $this->userService->createUser($data);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        Gate::authorize('update', $user);

        return Inertia::render('Admin/Users/Edit', [
            'user' => UserData::fromModel($user)->toArray(),
            'available_roles' => Role::pluck('name')->toArray(),
        ]);
    }

    public function update(UserData $data, User $user)
    {
        Gate::authorize('update', $user);

        $this->userService->updateUser($user, $data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        Gate::authorize('delete', $user);

        $this->userService->deleteUser($user);

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }

    public function restore($id)
    {
        return $this->performRestore($id, User::class, 'userService', 'restoreUser');
    }

    public function forceDelete($id)
    {
        return $this->performForceDelete($id, User::class, 'userService', 'forceDeleteUser');
    }

    public function bulkDestroy(Request $request)
    {
        return $this->performBulkAction($request, User::class, 'userService', 'Users');
    }
}
