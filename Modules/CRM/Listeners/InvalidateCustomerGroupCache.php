<?php

namespace Modules\CRM\Listeners;

use Modules\CRM\Services\CRMCacheService;
use Modules\Shared\Events\CacheInvalidationRequested;

/**
 * Handle cache invalidation for customer group-related changes.
 *
 * Centralizes cache flushing logic that was previously scattered
 * across model observers, improving testability and separation of concerns.
 */
class InvalidateCustomerGroupCache
{
    /**
     * Handle the event.
     */
    public function handle(CacheInvalidationRequested $event): void
    {
        if ($event->tag !== 'customer_groups') {
            return;
        }

        CRMCacheService::flushCustomerGroupCaches();
        CRMCacheService::flushCustomerCaches();
    }
}
