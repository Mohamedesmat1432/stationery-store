<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface UserRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated users with optional filtering.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function syncRoles(User $user, array $roles): void;

    /**
     * Get the query for exporting users.
     */
    public function getExportQuery(): Builder;
}
