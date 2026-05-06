<?php

namespace Modules\Catalog\Listeners;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Modules\Catalog\Services\CatalogCacheService;
use Modules\Shared\Events\ResourceChanged;

class SyncCatalogCache
{
    /**
     * Handle the resource changed event.
     */
    public function handle(ResourceChanged $event): void
    {
        // When a category changes, flush category caches
        if ($event->modelClass === Category::class) {
            CatalogCacheService::flushCategoryCaches();
        }

        // When a product changes, flush product caches and categories (for product counts)
        if ($event->modelClass === Product::class) {
            CatalogCacheService::flushProductCaches();
        }

        // When a brand changes, flush brand caches and products
        if ($event->modelClass === Brand::class) {
            CatalogCacheService::flushBrandCaches();
            CatalogCacheService::flushProductCaches();
        }
    }
}
