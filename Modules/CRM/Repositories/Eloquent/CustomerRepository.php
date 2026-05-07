<?php

namespace Modules\CRM\Repositories\Eloquent;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Modules\CRM\Repositories\Contracts\CustomerRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface, ProtectsBulkResources
{
    use HandlesQueryBuilder;

    /**
     * Get IDs from the given set that are protected from deletion or modification.
     */
    public function getProtectedIds(array $ids): array
    {
        $user = Auth::user();

        return Customer::withTrashed()
            ->whereIn('id', $ids)
            ->cursor()
            ->filter(fn (Customer $customer) => $customer->isProtected($user))
            ->pluck('id')
            ->toArray();
    }

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
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->applyQueryBuilder(
            model: Customer::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::callback('group', function ($query, $value) {
                    $query->where('customer_group_id', $value);
                }),
                AllowedFilter::trashed('trash'),
            ],
            allowedIncludes: ['user', 'group', 'addresses', 'orders'],
            allowedSorts: ['total_spent', 'orders_count', 'created_at'],
            perPage: $perPage,
            with: ['user', 'group'],
            params: $params
        );
    }

    /**
     * Find a customer by ID.
     */
    public function findById(string|int $id): Customer
    {
        return Customer::with(['user', 'group', 'addresses'])->findOrFail($id);
    }

    /**
     * Get the query for exporting customers.
     */
    public function buildExportQuery(array $params = []): Builder
    {
        return $this->buildQueryBuilder(
            model: Customer::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::callback('group', function ($query, $value) {
                    $query->where('customer_group_id', $value);
                }),
                AllowedFilter::trashed('trash'),
            ],
            with: ['user', 'group'],
            params: $params
        )->getEloquentBuilder();
    }
}
