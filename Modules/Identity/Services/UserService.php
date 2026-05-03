<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Identity\Data\UserData;
use Modules\Identity\Exports\UsersExport;
use Modules\Identity\Imports\UsersImport;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Shared\Events\BulkOperationCompleted;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserService
{
    use HandlesBulkOperations, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {
    }

    protected function getRepository(): UserRepositoryInterface
    {
        return $this->userRepository;
    }

    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Get paginated users for the index view.
     */
    public function getUsersPaginated(array $params = [], int $perPage = 15): array
    {
        return IdentityCacheService::rememberUsers(
            $params,
            $perPage,
            fn() => $this->userRepository->paginate($perPage)
        );
    }

    /**
     * Create a new user with roles.
     */
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
            if (auth()->check() && !auth()->user()->hasRole(Role::ROLE_ADMIN)) {
                $roles = array_diff($roles, [Role::ROLE_ADMIN]);
            }

            $this->userRepository->syncRoles($user, $roles);

            return $user;
        });
    }

    /**
     * Update an existing user.
     */
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
            if (auth()->check() && !auth()->user()->hasRole(Role::ROLE_ADMIN)) {
                $roles = array_diff($roles, [Role::ROLE_ADMIN]);

                if ($user->hasRole(Role::ROLE_ADMIN)) {
                    $roles[] = Role::ROLE_ADMIN;
                }
            }

            $this->userRepository->syncRoles($user, $roles);

            return $user;
        });
    }

    /**
     * Delete a user if not protected.
     */
    public function deleteUser(User $user): bool
    {
        if ($this->isProtected($user)) {
            return false;
        }

        return DB::transaction(fn() => $this->userRepository->delete($user));
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restoreUser(User $user): bool
    {
        return DB::transaction(fn() => $this->userRepository->restore($user));
    }

    /**
     * Permanently delete a user.
     */
    public function forceDeleteUser(User $user): bool
    {
        if ($this->isProtected($user)) {
            return false;
        }

        return DB::transaction(fn() => $this->userRepository->forceDelete($user));
    }

    /**
     * Check if a user is protected from deletion/modification.
     */
    public function isProtected(Model|User $model): bool
    {
        return $model->isProtectedBy(auth()->user());
    }

    public function getAvailableForCustomer(?string $includeUserId = null)
    {
        return IdentityCacheService::getAvailableForCustomer($includeUserId);
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
        User::withoutEvents(fn() => Excel::import(new UsersImport, $file));

        event(new BulkOperationCompleted(User::class, 'import'));
    }
}
