<?php

namespace Modules\CRM\Listeners;

use Modules\CRM\Services\CRMCacheService;
use Modules\Shared\Events\CacheInvalidationRequested;

/**
 * Handle cache invalidation for customer-related changes.
 *
 * Centralizes cache flushing logic that was previously scattered
 * across model observers, improving testability and separation of concerns.
 */
class InvalidateCustomerCache
{
    /**
     * Handle the event.
     */
    public function handle(CacheInvalidationRequested $event): void
    {
        if ($event->tag !== 'customers') {
            return;
        }

        CRMCacheService::flushCustomerCaches();
        CRMCacheService::flushCustomerGroupCaches();

    }
}
