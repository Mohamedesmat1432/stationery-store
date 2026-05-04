<?php

namespace Modules\CRM\Observers;

use App\Models\CustomerGroup;
use Modules\Shared\Events\CacheInvalidationRequested;

class CustomerGroupObserver
{
    /**
     * Set to true to ensure events are dispatched AFTER the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Request cache invalidation on any customer group state change.
     */
    public function saved(CustomerGroup $customerGroup): void
    {
        CacheInvalidationRequested::dispatch('customer_groups');
    }

    public function deleted(CustomerGroup $customerGroup): void
    {
        CacheInvalidationRequested::dispatch('customer_groups');
    }

    public function restored(CustomerGroup $customerGroup): void
    {
        CacheInvalidationRequested::dispatch('customer_groups');
    }

    public function forceDeleted(CustomerGroup $customerGroup): void
    {
        CacheInvalidationRequested::dispatch('customer_groups');
    }
}
