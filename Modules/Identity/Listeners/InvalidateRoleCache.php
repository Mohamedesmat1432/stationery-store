<?php

namespace Modules\Identity\Listeners;

use Modules\Identity\Services\IdentityCacheService;
use Modules\Shared\Events\CacheInvalidationRequested;

/**
 * Handle cache invalidation for role-related changes.
 *
 * Centralizes cache flushing logic that was previously scattered
 * across model observers, improving testability and separation of concerns.
 */
class InvalidateRoleCache
{
    /**
     * Handle the event.
     */
    public function handle(CacheInvalidationRequested $event): void
    {
        if ($event->tag !== 'roles') {
            return;
        }

        IdentityCacheService::flushRoleCaches();
        IdentityCacheService::flushUserCaches();
    }
}
