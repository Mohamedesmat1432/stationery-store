<?php

namespace Modules\CRM\Repositories\Contracts;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface CustomerRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated customers with filtering.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * Get the query for exporting customers.
     */
    public function buildExportQuery(array $params = []): Builder;

    /**
     * Find a customer by ID.
     */
    public function findById(string|int $id): Customer;
}
