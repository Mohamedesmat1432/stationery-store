<?php

namespace Modules\Catalog\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface BrandRepositoryInterface extends RepositoryInterface
{
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Get all active brands ordered by sort_order.
     */
    public function allActive(): Collection;

    /**
     * Build a filtered query for exports (no pagination).
     */
    public function buildExportQuery(array $params = []): Builder;
}
