<?php

namespace Modules\Identity\Services;

use App\Models\Role;
use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Identity\Data\UserData;
use Modules\Identity\Exports\UsersExport;
use Modules\Identity\Imports\UsersImport;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Shared\Events\ResourceChanged;
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
            fn () => $this->userRepository->paginate($perPage)
        );
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

            $this->userRepository->syncRoles($user, $this->filterAssignableRoles($data->roles));

            ResourceChanged::dispatch(User::class, 'created', [$user->id]);

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

            if (filled($data->password)) {
                $updateData['password'] = Hash::make($data->password);
            }

            /** @var User $user */
            $user = $this->userRepository->update($user, $updateData);

            $this->userRepository->syncRoles($user, $this->filterAssignableRoles($data->roles, $user));

            ResourceChanged::dispatch(User::class, 'updated', [$user->id]);

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

        $result = $this->userRepository->delete($user);

        if ($result) {
            ResourceChanged::dispatch(User::class, 'deleted', [$user->id]);
        }

        return $result;
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restoreUser(User $user): bool
    {
        $result = $this->userRepository->restore($user);

        if ($result) {
            ResourceChanged::dispatch(User::class, 'restored', [$user->id]);
        }

        return $result;
    }

    /**
     * Permanently delete a user.
     */
    public function forceDeleteUser(User $user): bool
    {
        if ($this->isProtected($user)) {
            return false;
        }

        $result = $this->userRepository->forceDelete($user);

        if ($result) {
            ResourceChanged::dispatch(User::class, 'force_deleted', [$user->id]);
        }

        return $result;
    }

    /**
     * Check if a user is protected from deletion/modification.
     */
    public function isProtected(Model|User $model): bool
    {
        return $model->isProtectedBy(Auth::user());
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
        User::withoutEvents(fn () => Excel::import(new UsersImport, $file));

        ResourceChanged::dispatch(User::class, 'imported');
    }
}
