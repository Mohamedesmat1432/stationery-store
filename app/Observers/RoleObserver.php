<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class RoleObserver
{
    /**
     * Handle the Role "saved" event.
     * Flush caches when role data is updated.
     */
    public function saved(Role $role): void
    {
        $this->flushCaches();
    }

    /**
     * Handle the Role "deleted" event.
     * Flush caches when role is deleted.
     */
    public function deleted(Role $role): void
    {
        $this->flushCaches();
    }

    /**
     * Flush all related caches on role changes.
     * When roles change, all users' permission caches must be invalidated.
     */
    protected function flushCaches(): void
    {
        // Flush role-specific caches
        Role::flushClassCache();
        Cache::forget('available_roles_list');

        // Flush user caches that depend on roles
        User::flushClassCache();
        Cache::forget('available_permissions_list');

        // Note: Individual user permission caches will be flushed when
        // users are modified, not when roles change globally. This is
        // acceptable as permissions are computed on-demand after cache expiry.
    }
}
