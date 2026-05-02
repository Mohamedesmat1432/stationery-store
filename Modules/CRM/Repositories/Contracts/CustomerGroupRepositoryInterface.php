<?php

namespace Modules\CRM\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface CustomerGroupRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated customer groups with filtering.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function allActive(): Collection;

    public function getExportQuery(): Builder;
}
