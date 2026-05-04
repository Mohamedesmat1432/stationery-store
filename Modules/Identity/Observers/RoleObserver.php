<?php

namespace Modules\Identity\Observers;

use App\Models\Role;
use Modules\Shared\Events\CacheInvalidationRequested;

class RoleObserver
{
    /**
     * Set to true to ensure events are dispatched AFTER the database transaction commits.
     * Prevents stale cache if transaction rolls back.
     */
    public bool $afterCommit = true;

    /**
     * Request cache invalidation on any role state change.
     */
    public function saved(Role $role): void
    {
        CacheInvalidationRequested::dispatch('roles');
    }

    public function deleted(Role $role): void
    {
        CacheInvalidationRequested::dispatch('roles');
    }
}
