<?php

namespace Modules\Identity\Observers;

use App\Models\Permission;
use Modules\Identity\Services\IdentityCacheService;
use Spatie\Permission\PermissionRegistrar;

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
     *
     * Permission changes cascade to roles and users, so all three
     * cache groups must be invalidated along with Spatie's internal cache.
     */
    protected function flushCaches(): void
    {
        IdentityCacheService::flushPermissionCaches();
        IdentityCacheService::flushRoleCaches();
        IdentityCacheService::flushUserCaches();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
