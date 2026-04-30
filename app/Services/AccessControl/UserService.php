<?php

namespace App\Services\AccessControl;

use App\Data\AccessControl\UserData;
use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class UserService
{
    public function __construct(
        protected UserRepositoryInterface $userRepository
    ) {}

    public function getUsersPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(User::class)
            ->with('roles')
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->role($value);
                }),
                AllowedFilter::trashed('trash'),
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createUser(UserData $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = $this->userRepository->create([
                'name' => $data->name,
                'email' => $data->email,
                'password' => Hash::make($data->password),
            ]);

            $this->userRepository->syncRoles($user, $data->roles);

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

            $user = $this->userRepository->update($user, $updateData);

            $this->userRepository->syncRoles($user, $data->roles);

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

    public function bulkDeleteUsers(array $ids): bool
    {
        // Prevent deleting the current user
        $ids = array_diff($ids, [auth()->id()]);

        // If not admin, prevent deleting other admins
        if (! auth()->user()->hasRole('admin')) {
            $adminIds = User::role('admin')->whereIn('id', $ids)->pluck('id')->toArray();
            $ids = array_diff($ids, $adminIds);
        }

        if (empty($ids)) {
            return false;
        }

        return $this->userRepository->bulkDelete($ids);
    }

    public function bulkRestoreUsers(array $ids): bool
    {
        return $this->userRepository->bulkRestore($ids);
    }

    public function bulkForceDeleteUsers(array $ids): bool
    {
        // Prevent deleting the current user
        $ids = array_diff($ids, [auth()->id()]);

        // If not admin, prevent deleting other admins
        if (! auth()->user()->hasRole('admin')) {
            $adminIds = User::onlyTrashed()->role('admin')->whereIn('id', $ids)->pluck('id')->toArray();
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
}
