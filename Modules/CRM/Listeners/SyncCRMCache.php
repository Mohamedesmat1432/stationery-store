<?php

// Refreshed to trigger IDE indexing

namespace Modules\CRM\Listeners;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Modules\CRM\Services\CRMCacheService;
use Modules\Shared\Events\ResourceChanged;

class SyncCRMCache
{
    /**
     * Handle the resource changed event.
     */
    public function handle(ResourceChanged $event): void
    {
        if ($event->modelClass === Customer::class) {
            CRMCacheService::flushCustomerCaches();
        }

        if ($event->modelClass === CustomerGroup::class) {
            CRMCacheService::flushCustomerGroupCaches();
            // Customer lists may display group info
            CRMCacheService::flushCustomerCaches();
        }
    }
}
