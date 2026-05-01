<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionObserver
{
    /**
     * Handle the Permission "saved" event.
     * Flush caches when permission is created or updated.
     */
    public function saved(Permission $permission): void
    {
        $this->flushCaches();
    }

    /**
     * Handle the Permission "deleted" event.
     * Flush caches when permission is deleted.
     */
    public function deleted(Permission $permission): void
    {
        $this->flushCaches();
    }

    /**
     * Flush all related caches on permission changes.
     * When permissions change, all users and roles' caches must be invalidated.
     */
    protected function flushCaches(): void
    {
        // Flush permission-related caches
        Cache::forget('available_permissions_list');

        // Flush role caches that depend on permissions
        Role::flushClassCache();

        // Flush user caches that depend on permissions
        User::flushClassCache();
    }
}
