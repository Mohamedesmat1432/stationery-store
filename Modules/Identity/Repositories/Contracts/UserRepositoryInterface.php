<?php

namespace Modules\Identity\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated users with optional filtering.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Sync roles to a user.
     *
     * @param  array<string>  $roles
     */
    public function syncRoles(User $user, array $roles): void;

    /**
     * Get the query for exporting users.
     */
    public function buildExportQuery(array $params = []): Builder;

    /**
     * Get permission names for a user.
     *
     * @return array<string>
     */
    public function getPermissions(string $userId): array;

    /**
     * Get role names for a user.
     *
     * @return array<string>
     */
    public function getRoles(string $userId): array;

    /**
     * Get users available for customer assignment.
     */
    public function getAvailableForCustomer(?string $includeUserId = null): Collection;

    /**
     * Get IDs of admin users from the given set.
     *
     * @param  array<string>  $ids
     * @return array<string>
     */
    public function getAdminIds(array $ids, bool $withTrashed = false): array;

    /**
     * Toggle the active status of a user.
     */
    public function toggleActive(Model $model): bool;
}
