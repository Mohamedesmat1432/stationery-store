<?php

namespace Modules\Catalog\Repositories\Eloquent;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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
            model: Product::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::exact('sku'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('brand_id'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ],
            with: ['category', 'brand', 'prices', 'media'],
            withCount: [
                'wishlistItems as protection_wishlist_items_count',
                'orders as protection_orders_count' => fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']),
            ],
            params: $params
        );
    }

    /**
     * Build a filtered query for exports (no pagination).
     */
    public function buildExportQuery(array $params = []): Builder
    {
        return $this->buildQueryBuilder(
            model: Product::class,
            allowedFilters: [
                AllowedFilter::scope('search'),
                AllowedFilter::exact('sku'),
                AllowedFilter::exact('category_id'),
                AllowedFilter::exact('brand_id'),
                AllowedFilter::exact('is_active'),
                AllowedFilter::trashed('trash'),
            ],
            with: ['category', 'brand', 'prices', 'media'],
            withCount: [
                'wishlistItems as protection_wishlist_items_count',
                'orders as protection_orders_count' => fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']),
            ],
            params: $params
        )->getEloquentBuilder();
    }

    /**
     * Get IDs from the given set that are protected from deletion or modification.
     */
    public function getProtectedIds(array $ids): array
    {
        $user = Auth::user();

        return Product::withTrashed()
            ->withCount([
                'wishlistItems as protection_wishlist_items_count',
                'orders as protection_orders_count' => fn ($q) => $q->whereNotIn('status', ['cancelled', 'refunded']),
            ])
            ->whereIn('id', $ids)
            ->cursor()
            ->filter(fn (Product $product) => $product->isProtected($user))
            ->pluck('id')
            ->toArray();
    }
}
