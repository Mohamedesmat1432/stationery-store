<?php

namespace Modules\Identity\Listeners;

use Modules\Identity\Services\IdentityCacheService;
use Modules\Shared\Events\CacheInvalidationRequested;

/**
 * Handle cache invalidation for user-related changes.
 *
 * Centralizes cache flushing logic that was previously scattered
 * across model observers, improving testability and separation of concerns.
 */
class InvalidateUserCache
{
    /**
     * Handle the event.
     */
    public function handle(CacheInvalidationRequested $event): void
    {
        if ($event->tag !== 'users') {
            return;
        }

        if ($event->specificKey !== null) {
            IdentityCacheService::flushUserCache($event->specificKey);
        } else {
            IdentityCacheService::flushUserCaches();
        }
    }
}
