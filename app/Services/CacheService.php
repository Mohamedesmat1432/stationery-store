<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Centralized Cache Service following Laravel's official cache patterns.
 *
 * This service provides:
 * - Standardized cache key naming
 * - Consistent TTL values
 * - Proper cache tagging for logical grouping
 * - Centralized cache invalidation
 * - Redis driver optimization
 */
class CacheService
{
    // Standard TTL values (in seconds)
    public const TTL_SHORT = 300;      // 5 minutes - for volatile data
    public const TTL_MEDIUM = 3600;    // 1 hour - for moderately changing data
    public const TTL_LONG = 86400;    // 24 hours - for stable data
    public const TTL_FOREVER = null;  // Forever - for configuration/static data

    // Cache key prefixes for consistency
    private const PREFIX_USER = 'user';
    private const PREFIX_ROLE = 'role';
    private const PREFIX_PERMISSION = 'permission';
    private const PREFIX_CUSTOMER_GROUP = 'customer_group';
    private const PREFIX_DISCOUNT = 'discount';
    private const PREFIX_CATEGORY = 'category';
    private const PREFIX_PRODUCT = 'product';
    private const PREFIX_ORDER = 'order';
    private const PREFIX_WISHLIST = 'wishlist';
    private const PREFIX_SETTING = 'setting';
    private const PREFIX_TRANSLATION = 'translation';

    // Cache tags for logical grouping
    private const TAG_USERS = 'users';
    private const TAG_ROLES = 'roles';
    private const TAG_PERMISSIONS = 'permissions';
    private const TAG_CUSTOMER_GROUPS = 'customer_groups';
    private const TAG_DISCOUNTS = 'discounts';
    private const TAG_CATEGORIES = 'categories';
    private const TAG_PRODUCTS = 'products';
    private const TAG_ORDERS = 'orders';
    private const TAG_WISHLISTS = 'wishlists';
    private const TAG_SETTINGS = 'settings';
    private const TAG_TRANSLATIONS = 'translations';

    /**
     * Generate a standardized cache key.
     */
    private static function key(string $prefix, string $suffix, ?string $id = null): string
    {
        return $id ? "{$prefix}:{$id}:{$suffix}" : "{$prefix}:{$suffix}";
    }

    // ========== USER CACHE METHODS ==========

    /**
     * Get cached user permissions.
     */
    public static function getUserPermissions(string $userId): array
    {
        return Cache::tags([self::TAG_USERS, self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_USER, 'permissions', $userId),
                self::TTL_LONG,
                fn () => \App\Models\User::find($userId)?->getAllPermissions()->pluck('name')->toArray() ?? []
            );
    }

    /**
     * Get cached user roles.
     */
    public static function getUserRoles(string $userId): array
    {
        return Cache::tags([self::TAG_USERS, self::TAG_ROLES])
            ->remember(
                self::key(self::PREFIX_USER, 'roles', $userId),
                self::TTL_LONG,
                fn () => \App\Models\User::find($userId)?->getRoleNames()->toArray() ?? []
            );
    }

    /**
     * Check if user has a specific permission (cached).
     */
    public static function userHasPermission(string $userId, string $permission): bool
    {
        return in_array($permission, self::getUserPermissions($userId));
    }

    /**
     * Check if user is admin (cached).
     */
    public static function userIsAdmin(string $userId): bool
    {
        return in_array(\App\Enums\Role::ROLE_ADMIN, self::getUserRoles($userId));
    }

    /**
     * Get cached available roles list for forms.
     */
    public static function getAvailableRoles(): array
    {
        return Cache::tags([self::TAG_ROLES])
            ->remember(
                self::key(self::PREFIX_ROLE, 'available_list'),
                self::TTL_MEDIUM,
                fn () => \App\Models\Role::pluck('name')->toArray()
            );
    }

    /**
     * Flush all user-related caches.
     */
    public static function flushUserCaches(): void
    {
        Cache::tags([self::TAG_USERS])->flush();
    }

    // ========== ROLE CACHE METHODS ==========

    /**
     * Get cached role permissions.
     */
    public static function getRolePermissions(string $roleId): array
    {
        return Cache::tags([self::TAG_ROLES, self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_ROLE, 'permissions', $roleId),
                self::TTL_LONG,
                fn () => \App\Models\Role::with('permissions')->find($roleId)?->permissions->pluck('name')->toArray() ?? []
            );
    }

    /**
     * Get cached all roles with permissions.
     */
    public static function getAllRolesWithPermissions(): array
    {
        return Cache::tags([self::TAG_ROLES, self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_ROLE, 'all_with_permissions'),
                self::TTL_MEDIUM,
                function () {
                    return \App\Models\Role::with('permissions')
                        ->orderBy('name')
                        ->get()
                        ->map(fn ($role) => [
                            'id' => $role->id,
                            'name' => $role->name,
                            'permissions' => $role->permissions->pluck('name')->toArray(),
                        ])
                        ->toArray();
                }
            );
    }

    /**
     * Get cached available permissions list for forms.
     */
    public static function getAvailablePermissions(): array
    {
        return Cache::tags([self::TAG_PERMISSIONS])
            ->remember(
                self::key(self::PREFIX_PERMISSION, 'available_list'),
                self::TTL_MEDIUM,
                fn () => \App\Enums\PermissionName::values()
            );
    }

    /**
     * Flush all role-related caches.
     */
    public static function flushRoleCaches(): void
    {
        Cache::tags([self::TAG_ROLES])->flush();
    }

    // ========== PERMISSION CACHE METHODS ==========

    /**
     * Flush all permission-related caches.
     */
    public static function flushPermissionCaches(): void
    {
        Cache::tags([self::TAG_PERMISSIONS])->flush();
    }

    // ========== CUSTOMER GROUP CACHE METHODS ==========

    /**
     * Get cached active customer groups.
     */
    public static function getActiveCustomerGroups(): array
    {
        return Cache::tags([self::TAG_CUSTOMER_GROUPS])
            ->remember(
                self::key(self::PREFIX_CUSTOMER_GROUP, 'active'),
                self::TTL_MEDIUM,
                fn () => \App\Models\CustomerGroup::active()->get()->toArray()
            );
    }

    /**
     * Get cached available customer groups list.
     */
    public static function getAvailableCustomerGroups(): array
    {
        return Cache::tags([self::TAG_CUSTOMER_GROUPS])
            ->remember(
                self::key(self::PREFIX_CUSTOMER_GROUP, 'available_list'),
                self::TTL_MEDIUM,
                fn () => \App\Models\CustomerGroup::active()->pluck('name', 'id')->toArray()
            );
    }

    /**
     * Flush all customer group caches.
     */
    public static function flushCustomerGroupCaches(): void
    {
        Cache::tags([self::TAG_CUSTOMER_GROUPS])->flush();
    }

    // ========== DISCOUNT CACHE METHODS ==========

    /**
     * Get cached active coupons.
     */
    public static function getActiveCoupons(): array
    {
        return Cache::tags([self::TAG_DISCOUNTS])
            ->remember(
                self::key(self::PREFIX_DISCOUNT, 'active_coupons'),
                self::TTL_SHORT, // Coupons can change frequently
                function () {
                    return \App\Models\Discount::active()
                        ->where('type', \App\Enums\DiscountType::COUPON)
                        ->get()
                        ->map(fn ($discount) => [
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
                fn () => \App\Models\Category::root()->with('children')->get()->toArray()
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
                    $order = \App\Models\Order::find($orderId);
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
                fn () => (int) \App\Models\Wishlist::find($wishlistId)?->items()->count() ?? 0
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
                fn () => \App\Models\Setting::where('key', $key)->first()?->getTypedValue() ?? $default
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
     * Check if user has cached permission.
     */
    public static function userHasPermission(int $userId, string $permission): bool
    {
        $permissions = self::getUserPermissions($userId);
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has admin role (bypass cache for security).
     */
    public static function userIsAdmin(int $userId): bool
    {
        return \App\Models\User::find($userId)?->hasRole(\App\Models\Role::ROLE_ADMIN) ?? false;
    }

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
            'store' => config('cache.stores.' . config('cache.default') . '.driver'),
            'tags_supported' => Cache::getStore() instanceof \Illuminate\Cache\TaggableStore,
        ];
    }
}