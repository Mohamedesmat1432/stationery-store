<?php

namespace Modules\CRM\Listeners;

use App\Models\Customer;
use App\Models\CustomerGroup;
use Modules\CRM\Services\CRMCacheService;
use Modules\Shared\Events\BulkOperationCompleted;

class FlushCRMCacheListener
{
    /**
     * Handle the event.
     */
    public function handle(BulkOperationCompleted $event): void
    {
        if (in_array($event->modelClass, [Customer::class, CustomerGroup::class])) {
            CRMCacheService::flushCustomerCaches();
            CRMCacheService::flushCustomerGroupCaches();
        }
    }
}
