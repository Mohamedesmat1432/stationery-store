<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Identity\Data\UserData;
use Modules\Identity\Exports\UsersExport;
use Modules\Identity\Imports\UsersImport;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
use Modules\Shared\Services\Concerns\HandlesBulkOperations;
use Modules\Shared\Services\Concerns\HandlesResourceOperations;
use Modules\Shared\Services\Concerns\ProtectsSystemResources;
use Modules\Shared\Services\Logging\ModuleLogger;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserService
{
    use HandlesBulkOperations, HandlesResourceOperations, ModuleLogger, ProtectsSystemResources {
        ProtectsSystemResources::filterBulkIds insteadof HandlesBulkOperations;
    }

    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

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
            fn () => $this->userRepository->paginate($perPage, $params)
        );
    }

    public function createUser(UserData $data): User
    {
        try {
            return DB::transaction(function () use ($data) {
                /** @var User $user */
                $user = $this->userRepository->create([
                    'name' => $data->name,
                    'email' => $data->email,
                    'password' => Hash::make($data->password),
                ]);

                $this->userRepository->syncRoles($user, $this->filterAssignableRoles($data->roles));

                ResourceChanged::dispatch(User::class, 'created', [$user->id]);

                return $user;
            });
        } catch (\Throwable $e) {
            $this->logError('Failed to create user', ['email' => $data->email], $e);
            throw $e;
        }
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, UserData $data): User
    {
        try {
            return DB::transaction(function () use ($user, $data) {
                $updateData = [
                    'name' => $data->name,
                    'email' => $data->email,
                ];

                if (filled($data->password)) {
                    $updateData['password'] = Hash::make($data->password);
                }

                /** @var User $user */
                $user = $this->userRepository->update($user, $updateData);

                $this->userRepository->syncRoles($user, $this->filterAssignableRoles($data->roles, $user));

                ResourceChanged::dispatch(User::class, 'updated', [$user->id]);

                return $user;
            });
        } catch (\Throwable $e) {
            $this->logError('Failed to update user', ['id' => $user->id, 'email' => $user->email], $e);
            throw $e;
        }
    }

    /**
     * Delete a user if not protected.
     */
    public function deleteUser(User $user): bool
    {
        return $this->performDelete($user);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restoreUser(User $user): bool
    {
        return $this->performRestore($user);
    }

    /**
     * Permanently delete a user.
     */
    public function forceDeleteUser(User $user): bool
    {
        return $this->performForceDelete($user);
    }

    /**
     * Check if a user is protected from deletion/modification.
     */
    public function isProtected(Model|User $model): bool
    {
        return $model->isProtected(Auth::user());
    }

    /**
     * Filter roles to ensure non-admins cannot assign/remove admin role.
     */
    protected function filterAssignableRoles(array $roles, ?User $existingUser = null): array
    {
        $currentUser = Auth::user();

        if (! $currentUser || $currentUser->hasRole(Role::ROLE_ADMIN)) {
            return $roles;
        }

        $roles = array_diff($roles, [Role::ROLE_ADMIN]);

        if ($existingUser && $existingUser->hasRole(Role::ROLE_ADMIN)) {
            $roles[] = Role::ROLE_ADMIN;
        }

        return array_values(array_unique($roles));
    }

    public function getAvailableForCustomer(?string $includeUserId = null)
    {
        return IdentityCacheService::getAvailableForCustomer($includeUserId);
    }

    public function exportUsers(array $columns, string $formatKey, array $params = []): BinaryFileResponse
    {
        $format = $formatKey === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;
        $extension = $formatKey === 'csv' ? 'csv' : 'xlsx';

        $query = $this->userRepository->buildExportQuery($params);

        return Excel::download(
            new UsersExport($query, $columns),
            'users.'.$extension,
            $format
        );
    }

    public function importUsers(UploadedFile $file): void
    {
        User::withoutEvents(fn () => Excel::import(new UsersImport, $file));

        ResourceChanged::dispatch(User::class, 'imported');
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleActive(User $user): bool
    {
        $result = $this->userRepository->toggleActive($user);

        if ($result) {
            ResourceChanged::dispatch(User::class, 'updated', [$user->id]);
        }

        return $result;
    }
}
