<?php

namespace Modules\Catalog\Repositories\Eloquent;

use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
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
            model: Brand::class,
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
     * Build a filtered query for exports (no pagination).
     */
    public function buildExportQuery(array $params = []): Builder
    {
        return $this->buildQueryBuilder(
            model: Brand::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::exact('slug'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ],
            allowedSorts: ['name', 'created_at', 'sort_order'],
            with: ['media'],
            withCount: ['products'],
            params: $params
        )->getEloquentBuilder();
    }

    /**
     * Get all active brands ordered by sort_order.
     */
    public function allActive(): Collection
    {
        return Brand::active()->orderBy('sort_order')->get();
    }

    /**
     * Get IDs from the given set that are protected from deletion or modification.
     */
    public function getProtectedIds(array $ids): array
    {
        $user = Auth::user();

        return Brand::withTrashed()
            ->withCount('products')
            ->whereIn('id', $ids)
            ->cursor()
            ->filter(fn (Brand $brand) => $brand->isProtected($user))
            ->pluck('id')
            ->toArray();
    }
}
