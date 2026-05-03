<?php

namespace Modules\Identity\Observers;

use App\Models\User;
use Modules\Identity\Services\IdentityCacheService;

class UserObserver
{
    /**
     * Set to true to ensure cache is cleared AFTER the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        IdentityCacheService::flushUserCaches();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        IdentityCacheService::flushUserCaches();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        IdentityCacheService::flushUserCaches();
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        IdentityCacheService::flushUserCaches();
    }
}
