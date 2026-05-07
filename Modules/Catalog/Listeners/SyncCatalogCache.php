<?php

namespace Modules\Catalog\Listeners;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Modules\Catalog\Services\CatalogCacheService;
use Modules\Shared\Events\ResourceChanged;

class SyncCatalogCache
{
    /**
     * Handle the resource changed event.
     */
    public function handle(ResourceChanged $event): void
    {
        // When a category changes, flush category caches AND product caches
        // because products display category info and counts
        if ($event->modelClass === Category::class) {
            CatalogCacheService::flushCategoryCaches();
            CatalogCacheService::flushProductCaches();
        }

        // When a product changes, flush product caches and categories (for product counts)
        if ($event->modelClass === Product::class) {
            CatalogCacheService::flushProductCaches();
            CatalogCacheService::flushCategoryCaches();
        }

        // When a brand changes, flush brand caches and products
        if ($event->modelClass === Brand::class) {
            CatalogCacheService::flushBrandCaches();
            CatalogCacheService::flushProductCaches();
        }

        // When an attribute changes, flush attribute and product caches
        if ($event->modelClass === Attribute::class || $event->modelClass === AttributeValue::class) {
            CatalogCacheService::flushAttributeCaches();
            CatalogCacheService::flushProductCaches();
        }

        // When a tag changes, flush product caches
        if ($event->modelClass === Tag::class) {
            CatalogCacheService::flushProductCaches();
        }
    }
}
