<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Modules\Shared\Services\Cache\BaseCacheService;

/**
 * Centralized Cache Service following Laravel's official cache patterns.
 * Legacy store for unmigrated domains.
 * Refactored to Pure Versioning (No Tags) for maximum reliability.
 */
class CacheService extends BaseCacheService
{
    // Cache key prefixes
    private const PREFIX_DISCOUNT = 'discount';

    private const PREFIX_CATEGORY = 'category';

    private const PREFIX_ORDER = 'order';

    private const PREFIX_WISHLIST = 'wishlist';

    private const PREFIX_SETTING = 'setting';

    private const PREFIX_TRANSLATION = 'translation';

    // Virtual Tags (for versioning)
    public const TAG_DISCOUNTS = 'discounts';

    public const TAG_CATEGORIES = 'categories';

    public const TAG_ORDERS = 'orders';

    public const TAG_WISHLISTS = 'wishlists';

    public const TAG_SETTINGS = 'settings';

    public const TAG_TRANSLATIONS = 'translations';

    public const TTL_SHORT = 300;

    public const TTL_MEDIUM = 3600;

    // ========== DISCOUNT CACHE METHODS ==========

    public static function getActiveDiscounts(): array
    {
        return self::rememberDirect(
            self::TAG_DISCOUNTS,
            'active',
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
            },
            null,
            self::TTL_SHORT
        );
    }

    public static function flushDiscountCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_DISCOUNTS]);
    }

    // ========== CATEGORY CACHE METHODS ==========

    public static function getCategoryTree(): array
    {
        return self::rememberDirect(
            self::TAG_CATEGORIES,
            'tree',
            fn () => Category::root()->with('children')->get()->toArray(),
            null,
            self::TTL_MEDIUM
        );
    }

    public static function flushCategoryCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_CATEGORIES]);
    }

    // ========== SETTING CACHE METHODS ==========

    public static function getSetting(string $key, mixed $default = null): mixed
    {
        return self::rememberDirect(
            self::TAG_SETTINGS,
            "key:{$key}",
            fn () => Setting::where('key', $key)->first()?->getTypedValue() ?? $default,
            null,
            86400 * 30
        );
    }

    public static function flushSettingCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_SETTINGS]);
    }

    // ========== TRANSLATION CACHE METHODS ==========

    public static function getTranslations(string $locale): array
    {
        return self::rememberDirect(
            self::TAG_TRANSLATIONS,
            "locale:{$locale}",
            function () use ($locale) {
                $file = base_path("lang/{$locale}.json");

                return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
            },
            null,
            86400 * 30
        );
    }

    public static function flushTranslationCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_TRANSLATIONS]);
    }

    /**
     * Clear all application caches.
     */
    public static function flushAllCaches(): void
    {
        Cache::flush();
        self::incrementTagVersion(self::TAG_GLOBAL);
    }
}
