<?php

namespace App\Observers;

use App\Models\CustomerGroup;

class CustomerGroupObserver
{
    /**
     * Handle the CustomerGroup "saved" event.
     */
    public function saved(CustomerGroup $customerGroup): void
    {
        // When a group changes, we might need to invalidate related data.
        // For now, the BaseModel handles the group's own Redis cache.
    }

    /**
     * Handle the CustomerGroup "deleted" event.
     */
    public function deleted(CustomerGroup $customerGroup): void
    {
        // Handle cascading logic if necessary
    }
}
