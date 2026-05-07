<?php

namespace Modules\Catalog\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface ProductRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated products with filters.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Get IDs from the given set that are protected.
     */
    public function getProtectedIds(array $ids): array;

    /**
     * Build a filtered query for exports (no pagination).
     */
    public function buildExportQuery(array $params = []): Builder;
}
