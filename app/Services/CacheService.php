<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Order;
use App\Models\Setting;
use App\Models\Wishlist;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\Cache;
use Modules\Shared\Services\Cache\BaseCacheService;

/**
 * Centralized Cache Service following Laravel's official cache patterns.
 * Legacy store for unmigrated domains.
 */
class CacheService extends BaseCacheService
{
    // Cache key prefixes for consistency
    private const PREFIX_DISCOUNT = 'discount';

    private const PREFIX_CATEGORY = 'category';

    private const PREFIX_PRODUCT = 'product';

    private const PREFIX_ORDER = 'order';

    private const PREFIX_WISHLIST = 'wishlist';

    private const PREFIX_SETTING = 'setting';

    private const PREFIX_TRANSLATION = 'translation';

    // Cache tags for logical grouping
    private const TAG_DISCOUNTS = 'discounts';

    private const TAG_CATEGORIES = 'categories';

    private const TAG_PRODUCTS = 'products';

    private const TAG_ORDERS = 'orders';

    private const TAG_WISHLISTS = 'wishlists';

    private const TAG_SETTINGS = 'settings';

    private const TAG_TRANSLATIONS = 'translations';

    // ========== DISCOUNT CACHE METHODS ==========

    /**
     * Get cached active discounts.
     */
    public static function getActiveDiscounts(): array
    {
        return Cache::tags([self::TAG_DISCOUNTS])
            ->remember(
                self::key(self::PREFIX_DISCOUNT, 'active'),
                self::TTL_SHORT, // Discounts can change frequently
                function () {
                    return Discount::active()
                        ->get()
                        ->map(fn (Discount $discount) => [
                            'id' => $discount->id,
                            'code' => $discount->code,
                            'type' => $discount->type->value,
                            'value' => $discount->value,
                            'is_valid' => $discount->isValid(),
                        ])
                        ->toArray();
                }
            );
    }

    /**
     * Flush all discount caches.
     */
    public static function flushDiscountCaches(): void
    {
        Cache::tags([self::TAG_DISCOUNTS])->flush();
    }

    // ========== CATEGORY CACHE METHODS ==========

    /**
     * Get cached category tree.
     */
    public static function getCategoryTree(): array
    {
        return Cache::tags([self::TAG_CATEGORIES])
            ->remember(
                self::key(self::PREFIX_CATEGORY, 'tree'),
                self::TTL_MEDIUM,
                fn () => Category::root()->with('children')->get()->toArray()
            );
    }

    /**
     * Flush all category caches.
     */
    public static function flushCategoryCaches(): void
    {
        Cache::tags([self::TAG_CATEGORIES])->flush();
    }

    // ========== ORDER CACHE METHODS ==========

    /**
     * Get cached order summary.
     */
    public static function getOrderSummary(string $orderId): array
    {
        return Cache::tags([self::TAG_ORDERS])
            ->remember(
                self::key(self::PREFIX_ORDER, 'summary', $orderId),
                self::TTL_SHORT, // Order status can change
                function () use ($orderId) {
                    $order = Order::find($orderId);

                    return $order ? [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status->value,
                        'grand_total' => $order->grand_total,
                        'item_count' => $order->items()->count(),
                    ] : [];
                }
            );
    }

    /**
     * Flush all order caches.
     */
    public static function flushOrderCaches(): void
    {
        Cache::tags([self::TAG_ORDERS])->flush();
    }

    // ========== WISHLIST CACHE METHODS ==========

    /**
     * Get cached wishlist item count.
     */
    public static function getWishlistItemCount(string $wishlistId): int
    {
        return Cache::tags([self::TAG_WISHLISTS])
            ->remember(
                self::key(self::PREFIX_WISHLIST, 'item_count', $wishlistId),
                self::TTL_SHORT, // Wishlist contents change frequently
                fn () => (int) Wishlist::find($wishlistId)?->items()->count() ?? 0
            );
    }

    /**
     * Flush all wishlist caches.
     */
    public static function flushWishlistCaches(): void
    {
        Cache::tags([self::TAG_WISHLISTS])->flush();
    }

    // ========== SETTING CACHE METHODS ==========

    /**
     * Get cached setting value.
     */
    public static function getSetting(string $key, mixed $default = null): mixed
    {
        return Cache::tags([self::TAG_SETTINGS])
            ->rememberForever(
                self::key(self::PREFIX_SETTING, $key),
                fn () => Setting::where('key', $key)->first()?->getTypedValue() ?? $default
            );
    }

    /**
     * Flush all setting caches.
     */
    public static function flushSettingCaches(): void
    {
        Cache::tags([self::TAG_SETTINGS])->flush();
    }

    // ========== TRANSLATION CACHE METHODS ==========

    /**
     * Get cached translations for a locale.
     */
    public static function getTranslations(string $locale): array
    {
        return Cache::tags([self::TAG_TRANSLATIONS])
            ->rememberForever(
                self::key(self::PREFIX_TRANSLATION, $locale),
                function () use ($locale) {
                    $file = base_path("lang/{$locale}.json");

                    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
                }
            );
    }

    /**
     * Flush all translation caches.
     */
    public static function flushTranslationCaches(): void
    {
        Cache::tags([self::TAG_TRANSLATIONS])->flush();
    }

    // ========== UTILITY METHODS ==========

    /**
     * Clear all application caches.
     */
    public static function flushAllCaches(): void
    {
        Cache::flush();
    }

    /**
     * Get cache statistics (for debugging).
     */
    public static function getCacheStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'store' => config('cache.stores.'.config('cache.default').'.driver'),
            'tags_supported' => Cache::getStore() instanceof TaggableStore,
        ];
    }
}
