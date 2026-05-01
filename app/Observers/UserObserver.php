<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "saved" event.
     */
    public function saved(User $user): void
    {
        $user->flushPermissionCache();
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $user->flushPermissionCache();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $user->flushPermissionCache();
    }
}
