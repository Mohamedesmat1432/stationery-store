<?php

namespace Modules\Identity\Observers;

use App\Models\Role;
use Modules\Identity\Services\IdentityCacheService;
use Spatie\Permission\PermissionRegistrar;

class RoleObserver
{
    /**
     * Handle the Role "saved" event.
     * Flush caches when role data is created or updated.
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
     *
     * Uses CacheService (Cache::tags) for application-level caches
     * and Spatie's PermissionRegistrar for the permission package cache.
     */
    protected function flushCaches(): void
    {
        IdentityCacheService::flushRoleCaches();
        IdentityCacheService::flushUserCaches();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
