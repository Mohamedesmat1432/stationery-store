<?php

namespace Modules\Catalog\Repositories\Eloquent;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Modules\Catalog\Repositories\Contracts\BrandRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;

class BrandRepository extends BaseRepository implements BrandRepositoryInterface, ProtectsBulkResources
{
    use HandlesQueryBuilder;

    protected function getModelClass(): string
    {
        return Brand::class;
    }

    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->applyQueryBuilder(
            model: Brand::withoutGlobalScopes(),
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::exact('slug'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ],
            allowedSorts: ['name', 'created_at', 'sort_order'],
            perPage: $perPage,
            with: ['media'],
            withCount: ['products'],
            params: $params
        );
    }

    /**
     * Get IDs from the given set that are protected from deletion or modification.
     */
    public function getProtectedIds(array $ids): array
    {
        return Brand::withTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (Brand $brand) => $brand->shouldBeProtected(Auth::user()))
            ->pluck('id')
            ->toArray();
    }
}
