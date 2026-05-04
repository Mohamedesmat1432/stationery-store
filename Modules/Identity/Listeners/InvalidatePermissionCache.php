<?php

namespace Modules\Identity\Listeners;

use Modules\Identity\Services\IdentityCacheService;
use Modules\Shared\Events\CacheInvalidationRequested;

/**
 * Handle cache invalidation for permission-related changes.
 *
 * Centralizes cache flushing logic that was previously scattered
 * across model observers, improving testability and separation of concerns.
 */
class InvalidatePermissionCache
{
    /**
     * Handle the event.
     */
    public function handle(CacheInvalidationRequested $event): void
    {
        if ($event->tag !== 'permissions') {
            return;
        }

        IdentityCacheService::flushPermissionCaches();

        // Also flush role and user caches since roles display permissions
        // and users have roles with permissions.
        IdentityCacheService::flushRoleCaches();
        IdentityCacheService::flushUserCaches();
    }
}
