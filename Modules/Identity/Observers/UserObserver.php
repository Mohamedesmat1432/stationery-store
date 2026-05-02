<?php

namespace Modules\Identity\Observers;

use App\Models\User;
use Modules\Identity\Services\IdentityCacheService;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        IdentityCacheService::flushUserCache($user->id);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        IdentityCacheService::flushUserCache($user->id);
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        IdentityCacheService::flushUserCache($user->id);
    }

    /**
     * Handle the User "forceDeleted" event.
     */
    public function forceDeleted(User $user): void
    {
        IdentityCacheService::flushUserCache($user->id);
    }
}
