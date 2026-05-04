<?php

namespace Modules\Identity\Observers;

use App\Models\User;
use Modules\Shared\Events\CacheInvalidationRequested;

class UserObserver
{
    /**
     * Set to true to ensure events are dispatched AFTER the database transaction commits.
     */
    public bool $afterCommit = true;

    /**
     * Request cache invalidation for the specific user on any state change.
     */
    public function saved(User $user): void
    {
        CacheInvalidationRequested::dispatch('users', $user->id);
    }

    public function deleted(User $user): void
    {
        CacheInvalidationRequested::dispatch('users', $user->id);
    }

    public function restored(User $user): void
    {
        CacheInvalidationRequested::dispatch('users', $user->id);
    }

    public function forceDeleted(User $user): void
    {
        CacheInvalidationRequested::dispatch('users', $user->id);
    }
}
