<?php

namespace Modules\CRM\Observers;

use App\Models\Customer;
use Modules\Identity\Services\IdentityCacheService;

class CustomerObserver
{
    /**
     * Handle the Customer "saved" event.
     */
    public function saved(Customer $customer): void
    {
        if ($customer->user_id) {
            IdentityCacheService::flushUserCache($customer->user_id);
        }
    }

    /**
     * Handle the Customer "deleted" event.
     */
    public function deleted(Customer $customer): void
    {
        if ($customer->user_id) {
            IdentityCacheService::flushUserCache($customer->user_id);
        }
    }

    /**
     * Handle the Customer "restored" event.
     */
    public function restored(Customer $customer): void
    {
        if ($customer->user_id) {
            IdentityCacheService::flushUserCache($customer->user_id);
        }
    }

    /**
     * Handle the Customer "forceDeleted" event.
     */
    public function forceDeleted(Customer $customer): void
    {
        if ($customer->user_id) {
            IdentityCacheService::flushUserCache($customer->user_id);
        }
    }
}
