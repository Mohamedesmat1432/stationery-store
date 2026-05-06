<?php

namespace Modules\Catalog\Repositories\Eloquent;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Modules\Catalog\Repositories\Contracts\ProductRepositoryInterface;
use Modules\Shared\Repositories\Concerns\HandlesQueryBuilder;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;
use Modules\Shared\Repositories\Eloquent\BaseRepository;
use Spatie\QueryBuilder\AllowedFilter;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface, ProtectsBulkResources
{
    use HandlesQueryBuilder;

    protected function getModelClass(): string
    {
        return Product::class;
    }

    /**
     * Get paginated products with filters and eager loading.
     */
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->applyQueryBuilder(
            model: Product::withoutGlobalScopes(),
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::exact('sku'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('brand_id'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ],
            allowedIncludes: ['category', 'brand', 'media', 'prices'],
            allowedSorts: ['name', 'price', 'created_at', 'sku'],
            perPage: $perPage,
            with: ['category', 'brand', 'prices', 'media'],
            params: $params
        );
    }

    /**
     * Get IDs from the given set that are protected from deletion or modification.
     */
    public function getProtectedIds(array $ids): array
    {
        $user = Auth::user();

        return Product::withTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn (Product $product) => $product->shouldBeProtected($user))
            ->pluck('id')
            ->toArray();
    }
}
