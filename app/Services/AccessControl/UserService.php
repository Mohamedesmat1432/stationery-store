<?php

namespace App\Services\AccessControl;

use App\Data\AccessControl\UserData;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

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

            $this->userRepository->syncRoles($user, $data->roles);

            $user->flushPermissionCache();

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

            $this->userRepository->syncRoles($user, $data->roles);

            $user->flushPermissionCache();

            return $user;
        });
    }

    public function deleteUser(User $user): bool
    {
        return $this->userRepository->delete($user);
    }

    public function restoreUser(User $user): bool
    {
        return $this->userRepository->restore($user);
    }

    public function forceDeleteUser(User $user): bool
    {
        return $this->userRepository->forceDelete($user);
    }

    public function bulkDelete(array $ids): bool
    {
        // Prevent deleting the current user
        $ids = array_diff($ids, [auth()->id()]);

        // If not admin, prevent deleting other admins
        if (!auth()->user()->hasRole(Role::ROLE_ADMIN)) {
            $adminIds = User::role(Role::ROLE_ADMIN)->whereIn('id', $ids)->pluck('id')->toArray();
            $ids = array_diff($ids, $adminIds);
        }

        if (empty($ids)) {
            return false;
        }

        $deleted = $this->userRepository->bulkDelete($ids);
        if ($deleted) {
            User::flushRedisTag();
        }

        return $deleted;
    }

    public function bulkRestore(array $ids): bool
    {
        $restored = $this->userRepository->bulkRestore($ids);
        if ($restored) {
            User::flushRedisTag();
        }

        return $restored;
    }

    public function bulkForceDelete(array $ids): bool
    {
        // Prevent deleting the current user
        $ids = array_diff($ids, [auth()->id()]);

        // If not admin, prevent deleting other admins
        if (!auth()->user()->hasRole(Role::ROLE_ADMIN)) {
            $adminIds = User::onlyTrashed()->role(Role::ROLE_ADMIN)->whereIn('id', $ids)->pluck('id')->toArray();
            $ids = array_diff($ids, $adminIds);
        }

        if (empty($ids)) {
            return false;
        }

        $deleted = $this->userRepository->bulkForceDelete($ids);
        if ($deleted) {
            User::flushRedisTag();
        }

        return $deleted;
    }

    public function getAvailableForCustomer(?string $includeUserId = null)
    {
        return User::whereDoesntHave('customer')
            ->when($includeUserId, fn($query) => $query->orWhere('id', $includeUserId))
            ->get();
    }

    public function exportUsers(array $columns, string $formatKey): BinaryFileResponse
    {
        $format = $formatKey === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $extension = $formatKey === 'csv' ? 'csv' : 'xlsx';

        return Excel::download(
            new UsersExport($this->userRepository->getExportQuery(), $columns),
            'users.' . $extension,
            $format
        );
    }

    public function importUsers(UploadedFile $file): void
    {
        Excel::import(new UsersImport, $file);
    }
}
