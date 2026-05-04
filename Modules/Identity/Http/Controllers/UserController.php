<?php

namespace Modules\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Identity\Data\ExportUsersData;
use Modules\Identity\Data\ImportUsersData;
use Modules\Identity\Data\UserData;
use Modules\Identity\Services\IdentityCacheService;
use Modules\Identity\Services\UserService;
use Modules\Shared\Http\Controllers\Traits\HandlesBulkActions;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserController extends Controller
{
    use HandlesBulkActions;

    public function __construct(
        protected UserService $userService
    ) {}

    /**
     * Display a listing of the users.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('Admin/Users/Index', [
            'users' => $this->userService->getUsersPaginated($request->all()),
            'filters' => $request->only(['filter']),
            'available_roles' => Inertia::defer(fn () => IdentityCacheService::getAvailableRoles()),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('Admin/Users/Create', [
            'available_roles' => Inertia::defer(fn () => IdentityCacheService::getAvailableRoles()),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(UserData $data): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $this->userService->createUser($data);

        return to_route('admin.users.index')->with('success', __('User created successfully.'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): RedirectResponse
    {
        Gate::authorize('view', $user);

        return to_route('admin.users.edit', $user);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('Admin/Users/Edit', [
            'user' => UserData::fromUser($user->loadMissing('roles')),
            'available_roles' => Inertia::defer(fn () => IdentityCacheService::getAvailableRoles()),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserData $data, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $this->userService->updateUser($user, $data);

        return to_route('admin.users.index')->with('success', __('User updated successfully.'));
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $deleted = $this->userService->deleteUser($user);

        if (! $deleted) {
            return back()->with('error', __('This user is protected and cannot be deleted.'));
        }

        return to_route('admin.users.index')->with('success', __('User deleted successfully.'));
    }

    /**
     * Restore the specified user from storage.
     */
    public function restore(string $id): RedirectResponse
    {
        return $this->performRestore($id, User::class, 'userService', 'restoreUser');
    }

    /**
     * Permanently delete the specified user from storage.
     */
    public function forceDelete(string $id): RedirectResponse
    {
        return $this->performForceDelete($id, User::class, 'userService', 'forceDeleteUser');
    }

    /**
     * Handle bulk actions for users.
     */
    public function bulkAction(Request $request): RedirectResponse
    {
        return $this->performBulkAction($request, User::class, 'userService');
    }

    /**
     * Export users to a file.
     */
    public function export(ExportUsersData $data): BinaryFileResponse
    {
        Gate::authorize('export', User::class);

        return $this->userService->exportUsers($data->columns, $data->format);
    }

    /**
     * Import users from a file.
     */
    public function import(ImportUsersData $data): RedirectResponse
    {
        Gate::authorize('import', User::class);

        $this->userService->importUsers($data->file);

        return to_route('admin.users.index')->with('success', __('Users imported successfully.'));
    }
}
