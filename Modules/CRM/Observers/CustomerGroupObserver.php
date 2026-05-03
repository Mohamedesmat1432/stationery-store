<?php

namespace Modules\CRM\Observers;

use App\Models\CustomerGroup;
use Modules\CRM\Services\CRMCacheService;

class CustomerGroupObserver
{
    /**
     * Set to true to ensure cache is cleared AFTER the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Handle the CustomerGroup "saved" event.
     */
    public function saved(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
        CRMCacheService::flushCustomerCaches();
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     */
    public function deleted(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
        CRMCacheService::flushCustomerCaches();
    }

    /**
     * Handle the CustomerGroup "restored" event.
     */
    public function restored(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
        CRMCacheService::flushCustomerCaches();
    }

    /**
     * Handle the CustomerGroup "forceDeleted" event.
     */
    public function forceDeleted(CustomerGroup $customerGroup): void
    {
        CRMCacheService::flushCustomerGroupCaches();
        CRMCacheService::flushCustomerCaches();
    }
}
