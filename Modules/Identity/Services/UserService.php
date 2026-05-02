<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Identity\Data\UserData;
use Modules\Identity\Exports\UsersExport;
use Modules\Identity\Imports\UsersImport;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getUsersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($perPage);
    }

    public function createUser(UserData $data): User
    {
        return DB::transaction(function () use ($data) {
            /** @var User $user */
            $user = $this->userRepository->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);

            $roles = $data->roles;
            if (auth()->check() && ! auth()->user()->hasRole(Role::ROLE_ADMIN)) {
                $roles = array_diff($roles, [Role::ROLE_ADMIN]);
            }

            $this->userRepository->syncRoles($user, $roles);

            // Cache invalidation handled by UserObserver::saved()

            return $user;
        });
    }

    public function updateUser(User $user, UserData $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $updateData = [
                'name' => $data->name,
                'email' => $data->email,
            ];

            if ($data->password) {
                $updateData['password'] = Hash::make($data->password);
            }

            /** @var User $user */
            $user = $this->userRepository->update($user, $updateData);

            $roles = $data->roles;
            if (auth()->check() && ! auth()->user()->hasRole(Role::ROLE_ADMIN)) {
                // If they are updating an existing admin and somehow bypassed the policy,
                // we should not strip the admin role if the user already had it.
                // But generally, non-admins shouldn't assign it to anyone.
                $roles = array_diff($roles, [Role::ROLE_ADMIN]);

                // If the user already was an admin, preserve it (though Policy prevents this edit anyway)
                if ($user->hasRole(Role::ROLE_ADMIN)) {
                    $roles[] = Role::ROLE_ADMIN;
                }
            }

            $this->userRepository->syncRoles($user, $roles);

            // Cache invalidation handled by UserObserver::saved()

            return $user;
        });
    }

    public function deleteUser(User $user): bool
    {
        if (auth()->check() && auth()->id() === $user->id) {
            return false;
        }

        if ($user->hasRole(Role::ROLE_ADMIN) && auth()->check() && ! auth()->user()->hasRole(Role::ROLE_ADMIN)) {
            return false;
        }

        return $this->userRepository->delete($user);
    }

    public function restoreUser(User $user): bool
    {
        return $this->userRepository->restore($user);
    }

    public function forceDeleteUser(User $user): bool
    {
        if (auth()->check() && auth()->id() === $user->id) {
            return false;
        }

        if ($user->hasRole(Role::ROLE_ADMIN) && auth()->check() && ! auth()->user()->hasRole(Role::ROLE_ADMIN)) {
            return false;
        }

        return $this->userRepository->forceDelete($user);
    }

    public function bulkDelete(array $ids): bool
    {
        // Prevent deleting the current user
        $ids = array_diff($ids, [auth()->id()]);

        // If not admin, prevent deleting other admins
        if (! auth()->user()->hasRole(Role::ROLE_ADMIN)) {
            $adminIds = User::role(Role::ROLE_ADMIN)->whereIn('id', $ids)->pluck('id')->toArray();
            $ids = array_diff($ids, $adminIds);
        }

        if (empty($ids)) {
            return false;
        }

        return $this->userRepository->bulkDelete($ids);
    }

    public function bulkRestore(array $ids): bool
    {
        return $this->userRepository->bulkRestore($ids);
    }

    public function bulkForceDelete(array $ids): bool
    {
        // Prevent deleting the current user
        $ids = array_diff($ids, [auth()->id()]);

        // If not admin, prevent deleting other admins
        if (! auth()->user()->hasRole(Role::ROLE_ADMIN)) {
            $adminIds = User::onlyTrashed()->role(Role::ROLE_ADMIN)->whereIn('id', $ids)->pluck('id')->toArray();
            $ids = array_diff($ids, $adminIds);
        }

        if (empty($ids)) {
            return false;
        }

        return $this->userRepository->bulkForceDelete($ids);
    }

    public function getAvailableForCustomer(?string $includeUserId = null)
    {
        return User::whereDoesntHave('customer')
            ->when($includeUserId, fn ($query) => $query->orWhere('id', $includeUserId))
            ->get();
    }

    public function exportUsers(array $columns, string $formatKey): BinaryFileResponse
    {
        $format = $formatKey === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $extension = $formatKey === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(
            new UsersExport($this->userRepository->getExportQuery(), $columns),
            'users.'.$extension,
            $format
        );
    }

    public function importUsers(UploadedFile $file): void
    {
        Excel::import(new UsersImport, $file);
    }
}
