<?php

namespace Modules\CRM\Services;

use Modules\CRM\Data\CustomerData;
use Modules\CRM\Data\CustomerGroupData;
use Modules\CRM\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Modules\Shared\Services\Cache\BaseCacheService;

class CRMCacheService extends BaseCacheService
{
    public const TAG_CUSTOMERS = 'customers';

    public const TAG_CUSTOMER_GROUPS = 'customer_groups';

    /**
     * Remember paginated customers in cache.
     */
    public static function rememberCustomers(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_CUSTOMERS,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => CustomerData::collect($collection)
        );
    }

    /**
     * Remember paginated customer groups in cache.
     */
    public static function rememberCustomerGroups(array $params, int $perPage, callable $callback, ?callable $transform = null): array
    {
        return self::rememberPaginated(
            self::TAG_CUSTOMER_GROUPS,
            $params,
            $perPage,
            $callback,
            $transform ?? fn ($collection) => CustomerGroupData::collect($collection)
        );
    }

    /**
     * Get active customer groups from cache.
     */
    public static function getActiveCustomerGroups(): array
    {
        return self::rememberDirect(
            self::TAG_CUSTOMER_GROUPS,
            'active_list',
            fn () => resolve(CustomerGroupRepositoryInterface::class)->allActive(),
            fn ($collection) => CustomerGroupData::collect($collection)->toArray()
        );
    }

    /**
     * Flush all customer-related caches.
     */
    public static function flushCustomerCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_CUSTOMERS]);
    }

    /**
     * Flush all customer group-related caches.
     */
    public static function flushCustomerGroupCaches(): void
    {
        self::flushTagsWithFallbacks([self::TAG_CUSTOMER_GROUPS]);
    }
}
