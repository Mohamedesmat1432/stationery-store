<?php

namespace Modules\Identity\Listeners;

use App\Models\User;
use Modules\Identity\Services\IdentityCacheService;
use Spatie\Permission\PermissionRegistrar;

/**
 * Handles Spatie's role/permission attach/detach events.
 *
 * When roles or permissions are attached/detached from a user,
 * this listener invalidates the application-level cache and
 * Spatie's internal permission cache.
 */
class FlushUserPermissionCache
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (isset($event->model) && $event->model instanceof User) {
            IdentityCacheService::flushUserCache($event->model->id);
        } else {
            IdentityCacheService::flushUserCaches();
            IdentityCacheService::flushRoleCaches();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
