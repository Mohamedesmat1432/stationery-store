<?php

namespace Modules\CRM\Observers;

use App\Models\Customer;
use Modules\Shared\Events\CacheInvalidationRequested;

class CustomerObserver
{
    /**
     * Set to true to ensure events are dispatched AFTER the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Request cache invalidation on any customer state change.
     */
    public function saved(Customer $customer): void
    {
        CacheInvalidationRequested::dispatch('customers');
    }

    public function deleted(Customer $customer): void
    {
        CacheInvalidationRequested::dispatch('customers');
    }

    public function restored(Customer $customer): void
    {
        CacheInvalidationRequested::dispatch('customers');
    }

    public function forceDeleted(Customer $customer): void
    {
        CacheInvalidationRequested::dispatch('customers');
    }
}
