<?php

namespace Modules\CRM\Observers;

use App\Models\Customer;
use Modules\CRM\Services\CRMCacheService;

class CustomerObserver
{
    /**
     * Set to true to ensure cache is cleared AFTER the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Handle the Customer "saved" event.
     */
    public function saved(Customer $customer): void
    {
        CRMCacheService::flushCustomerCaches();
        CRMCacheService::flushCustomerGroupCaches();
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        CRMCacheService::flushCustomerCaches();
        CRMCacheService::flushCustomerGroupCaches();
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        CRMCacheService::flushCustomerCaches();
        CRMCacheService::flushCustomerGroupCaches();
    }

    /**
     * Handle the Customer "force deleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        CRMCacheService::flushCustomerCaches();
        CRMCacheService::flushCustomerGroupCaches();
    }
}
