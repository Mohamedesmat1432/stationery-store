<?php

namespace Modules\Identity\Observers;

use App\Models\Permission;
use Modules\Shared\Events\CacheInvalidationRequested;

class PermissionObserver
{
    /**
     * Set to true to ensure events are dispatched AFTER the database transaction commits.
     * Prevents stale cache if transaction rolls back.
     */
    public bool $afterCommit = true;

    /**
     * Request cache invalidation on any permission state change.
     */
    public function saved(Permission $permission): void
    {
        CacheInvalidationRequested::dispatch('permissions');
    }

    public function deleted(Permission $permission): void
    {
        CacheInvalidationRequested::dispatch('permissions');
    }
}
