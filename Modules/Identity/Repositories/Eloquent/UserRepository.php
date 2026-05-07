<?php

namespace Modules\Identity\Repositories\Eloquent;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Modules\Identity\Repositories\Contracts\UserRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;

class UserRepository extends BaseRepository implements ProtectsBulkResources, UserRepositoryInterface
{
    use HandlesQueryBuilder;

    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return User::class;
    }

    /**
     * Get paginated users with filters and eager loading.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->applyQueryBuilder(
            model: User::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->role($value);
                }),
                AllowedFilter::exact('email'),
                AllowedFilter::trashed('trash'),
            ],
            allowedIncludes: ['roles', 'customer'],
            allowedSorts: ['name', 'email', 'created_at'],
            perPage: $perPage,
            with: ['roles'],
            params: $params
        );
    }

    /**
     * Find a user by ID with roles.
     */
    public function findById(string|int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    /**
     * Sync roles to the given user.
     */
    public function syncRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }

    /**
     * Get the query for exporting users.
     */
    public function buildExportQuery(array $params = []): Builder
    {
        return $this->buildQueryBuilder(
            model: User::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::callback('role', function ($query, $value) {
                    $query->role($value);
                }),
                AllowedFilter::exact('email'),
                AllowedFilter::trashed('trash'),
            ],
            with: ['roles'],
            params: $params
        )->getEloquentBuilder();
    }

    /**
     * Get permission names for a user.
     */
    public function getPermissions(string $userId): array
    {
        return User::find($userId)?->getAllPermissions()->pluck('name')->toArray() ?? [];
    }

    /**
     * Get role names for a user.
     */
    public function getRoles(string $userId): array
    {
        return User::find($userId)?->getRoleNames()->toArray() ?? [];
    }

    /**
     * Get users available for customer assignment.
     */
    public function getAvailableForCustomer(?string $includeUserId = null): Collection
    {
        return User::with('roles')
            ->whereDoesntHave('customer')
            ->when($includeUserId, fn ($query) => $query->where(fn ($q) => $q->where('id', $includeUserId)))
            ->get();
    }

    /**
     * Get IDs of admin users from the given set.
     */
    public function getAdminIds(array $ids, bool $withTrashed = false): array
    {
        $query = User::query()
            ->role(Role::ROLE_ADMIN)
            ->whereIn('id', $ids);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->pluck('id')->toArray();
    }

    /**
     * Get IDs from the given set that are protected from deletion or modification.
     */
    public function getProtectedIds(array $ids): array
    {
        $currentUser = Auth::user();

        if (! $currentUser) {
            return [];
        }

        // Eager-load roles to prevent N+1 during protection check
        return User::withTrashed()
            ->whereIn('id', $ids)
            ->with('roles')
            ->cursor()
            ->filter(fn (User $user) => $user->isProtected($currentUser))
            ->pluck('id')
            ->toArray();
    }

    /**
     * Toggle the active status of a user.
     */
    public function toggleActive(Model $model): bool
    {
        /** @var User $model */
        $model->is_active = ! $model->is_active;

        return $model->save();
    }
}
