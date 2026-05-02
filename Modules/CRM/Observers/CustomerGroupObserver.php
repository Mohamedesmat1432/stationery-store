<?php

namespace Modules\CRM\Observers;

use App\Models\CustomerGroup;
use Modules\CRM\Services\CRMCacheService;

class CustomerGroupObserver
{
    /**
     * Handle the CustomerGroup "saved" event.
     */
    public function saved(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     */
    public function deleted(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
    }

    /**
     * Handle the CustomerGroup "restored" event.
     */
    public function restored(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
    }

    /**
     * Handle the CustomerGroup "forceDeleted" event.
     */
    public function forceDeleted(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
    }
}
