<?php

namespace Modules\Shared\Services\Cache;

abstract class BaseCacheService
{
    // Standard TTL values (in seconds)
    public const TTL_SHORT = 300;      // 5 minutes - for volatile data

    public const TTL_MEDIUM = 3600;    // 1 hour - for moderately changing data

    public const TTL_LONG = 86400;    // 24 hours - for stable data

    public const TTL_FOREVER = null;  // Forever - for configuration/static data

    /**
     * Generate a standardized cache key.
     */
    protected static function key(string $prefix, string $suffix, ?string $id = null): string
    {
        return $id ? "{$prefix}:{$id}:{$suffix}" : "{$prefix}:{$suffix}";
    }
}
