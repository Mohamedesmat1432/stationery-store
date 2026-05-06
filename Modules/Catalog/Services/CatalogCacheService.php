<?php

namespace Modules\Catalog\Services;

use App\Models\Brand;
use Modules\Catalog\Data\BrandData;
use Modules\Catalog\Data\CategoryData;
use Modules\Catalog\Data\ProductData;
use Modules\Shared\Services\Cache\BaseCacheService;

class CatalogCacheService extends BaseCacheService
{
    public const TAG_CATEGORIES = 'catalog:categories';

    public const TAG_PRODUCTS = 'catalog:products';

    public const TAG_BRANDS = 'catalog:brands';

    public const TAG_ATTRIBUTES = 'catalog:attributes';

    /**
     * Flush all category-related caches.
     */
    public static function flushCategoryCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_CATEGORIES]);
    }

    /**
     * Flush all product-related caches.
     */
    public static function flushProductCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_PRODUCTS, self::TAG_CATEGORIES]);
    }

    /**
     * Flush all brand-related caches.
     */
    public static function flushBrandCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_BRANDS]);
    }

    /**
     * Flush all attribute-related caches.
     */
    public static function flushAttributeCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_ATTRIBUTES]);
    }

    /**
     * Flush all catalog caches.
     */
    public static function flushAllCatalogCaches(): void
    {
        self::flushTagsWithFallbacks([
            self::TAG_CATEGORIES,
            self::TAG_PRODUCTS,
            self::TAG_BRANDS,
            self::TAG_ATTRIBUTES,
        ]);
    }

    /**
     * Remember products with Pre-Cache Transformation.
     */
    public static function rememberProducts(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_PRODUCTS,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => ProductData::collect($collection)
        );
    }

    /**
     * Remember categories with Pre-Cache Transformation.
     */
    public static function rememberCategories(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_CATEGORIES,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => CategoryData::collect($collection)
        );
    }

    /**
     * Remember brands with Pre-Cache Transformation.
     */
    public static function rememberBrands(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_BRANDS,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => BrandData::collect($collection)
        );
    }

    /**
     * Get available brands for selects (cached).
     */
    public static function getAvailableBrands(): array
    {
        return self::rememberDirect(
            self::TAG_BRANDS,
            'available_brands',
            fn () => Brand::active()->orderBy('sort_order')->get(['id', 'name']),
            fn ($collection) => $collection->toArray()
        );
    }
}
