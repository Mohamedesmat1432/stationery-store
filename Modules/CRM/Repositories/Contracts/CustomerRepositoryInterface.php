<?php

namespace Modules\CRM\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated customers with filtering.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get the query for exporting customers.
     */
    public function getExportQuery(): Builder;
}
