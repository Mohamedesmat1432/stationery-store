<?php

namespace Modules\CRM\Repositories\Eloquent;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\CRM\Repositories\Contracts\CustomerRepositoryInterface;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return Customer::class;
    }

    /**
     * Get paginated customers with filtering.
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Customer::class)
            ->with(['user', 'group'])
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('group', 'customer_group_id'),
                AllowedFilter::trashed('trash'),
            ])
            ->allowedIncludes(...['user', 'group', 'addresses', 'orders'])
            ->allowedSorts(...['total_spent', 'orders_count', 'created_at'])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find a customer by ID.
     */
    public function findById(string $id): Customer
    {
        return Customer::with(['user', 'group', 'addresses'])->findOrFail($id);
    }

    /**
     * Get the query for exporting customers.
     */
    public function getExportQuery(): Builder
    {
        return Customer::query()->with(['user', 'group']);
    }
}
