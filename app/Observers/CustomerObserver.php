<?php

namespace App\Observers;

use App\Models\Customer;

class CustomerObserver
{
    /**
     * Handle the Customer "saved" event.
     */
    public function saved(Customer $customer): void
    {
        $customer->user?->flushPermissionCache();
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        $customer->user?->flushPermissionCache();
    }
}
