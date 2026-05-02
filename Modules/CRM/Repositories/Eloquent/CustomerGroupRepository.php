<?php

namespace Modules\CRM\Repositories\Eloquent;

use App\Models\CustomerGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\CRM\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerGroupRepository extends BaseRepository implements CustomerGroupRepositoryInterface
{
    protected function getModelClass(): string
    {
        return CustomerGroup::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(CustomerGroup::class)
            ->withCount('customers')
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ])
            ->defaultSort('sort_order', '-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function allActive(): Collection
    {
        return CustomerGroup::active()->orderBy('sort_order')->get();
    }

    public function getExportQuery(): Builder
    {
        return CustomerGroup::query();
    }
}
