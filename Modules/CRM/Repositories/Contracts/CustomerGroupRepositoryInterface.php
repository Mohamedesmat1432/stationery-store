<?php

namespace Modules\CRM\Repositories\Contracts;

use App\Models\CustomerGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface CustomerGroupRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated customer groups with filtering.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Get all active customer groups.
     */
    public function allActive(): Collection;

    /**
     * Get the query for exporting customer groups.
     */
    public function buildExportQuery(array $params = []): Builder;

    /**
     * Find a customer group by ID.
     */
    public function findById(string|int $id): CustomerGroup;
}
