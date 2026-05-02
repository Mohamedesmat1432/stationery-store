<?php

namespace Modules\CRM\Services;

use App\Models\CustomerGroup;
use Illuminate\Support\Facades\Cache;
use Modules\Shared\Services\Cache\BaseCacheService;

class CRMCacheService extends BaseCacheService
{
    private const PREFIX_CUSTOMER_GROUP = 'customer_group';

    private const TAG_CUSTOMER_GROUPS = 'customer_groups';

    // ========== CUSTOMER GROUP CACHE METHODS ==========

    public static function getActiveCustomerGroups(): array
    {
        return Cache::tags([self::TAG_CUSTOMER_GROUPS])
            ->remember(
                self::key(self::PREFIX_CUSTOMER_GROUP, 'active'),
                self::TTL_MEDIUM,
                fn () => CustomerGroup::active()->get()->toArray()
            );
    }

    public static function getAvailableCustomerGroups(): array
    {
        return Cache::tags([self::TAG_CUSTOMER_GROUPS])
            ->remember(
                self::key(self::PREFIX_CUSTOMER_GROUP, 'available_list'),
                self::TTL_MEDIUM,
                fn () => CustomerGroup::active()->pluck('name', 'id')->toArray()
            );
    }

    public static function flushCustomerGroupCaches(): void
    {
        Cache::tags([self::TAG_CUSTOMER_GROUPS])->flush();
    }
}
