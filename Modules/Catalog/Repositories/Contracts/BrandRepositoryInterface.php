<?php

namespace Modules\Catalog\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

interface BrandRepositoryInterface extends RepositoryInterface
{
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;
}
