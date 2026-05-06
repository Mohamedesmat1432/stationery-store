<?php

namespace Modules\CRM\Repositories\Eloquent;

use App\Models\CustomerGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\CRM\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;

class CustomerGroupRepository extends BaseRepository implements CustomerGroupRepositoryInterface, ProtectsBulkResources
{
    use HandlesQueryBuilder;

    /**
     * Get the model class for this repository.
     */
    protected function getModelClass(): string
    {
        return CustomerGroup::class;
    }

    /**
     * Get paginated customer groups with filtering.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->applyQueryBuilder(
            model: CustomerGroup::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ],
            allowedSorts: ['name', 'sort_order', 'discount_percentage', 'created_at'],
            defaultSort: ['sort_order', '-id'],
            perPage: $perPage,
            withCount: ['customers'],
            params: $params
        );
    }

    /**
     * Get all active customer groups.
     */
    public function allActive(): Collection
    {
        return CustomerGroup::active()
            ->withCount('customers')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get the query for exporting customer groups.
     */
    public function getExportQuery(): Builder
    {
        return CustomerGroup::query();
    }

    /**
     * Find a customer group by ID.
     */
    public function findById(string $id): CustomerGroup
    {
        return CustomerGroup::withCount('customers')->findOrFail($id);
    }

    /**
     * Filter and return IDs of protected customer groups.
     */
    public function getProtectedIds(array $ids): array
    {
        return CustomerGroup::withTrashed()
            ->whereIn('id', $ids)
            ->cursor()
            ->filter(fn (CustomerGroup $group) => $group->isProtected())
            ->pluck('id')
            ->toArray();
    }
}
