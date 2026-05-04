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
        if (in_array($event->modelClass, [Customer::class, CustomerGroup::class])) {
            CRMCacheService::flushCustomerCaches();
            CRMCacheService::flushCustomerGroupCaches();
        }
    }
}
