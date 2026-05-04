<?php

namespace Modules\Identity\Listeners;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Modules\Identity\Services\IdentityCacheService;
use Modules\Shared\Events\ResourceChanged;
use Spatie\Permission\Events\PermissionAttachedEvent;
use Spatie\Permission\Events\PermissionDetachedEvent;
use Spatie\Permission\Events\RoleAttachedEvent;
use Spatie\Permission\Events\RoleDetachedEvent;
use Spatie\Permission\PermissionRegistrar;

class SyncIdentityCache
{
    /**
     * Handle the resource changed or access control event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof ResourceChanged) {
            $this->handleResourceChanged($event);

            return;
        }

        $this->handleAccessControlChanged($event);
    }

    /**
     * Handle standard CRUD resource changes.
     */
    protected function handleResourceChanged(ResourceChanged $event): void
    {
        if ($event->modelClass === User::class) {
            // For single user changes, we can be more surgical
            if (count($event->ids) === 1 && ! str_starts_with($event->action, 'bulk')) {
                IdentityCacheService::flushUserCache($event->ids[0]);
            } else {
                IdentityCacheService::flushUserCaches();
            }
        }

        if ($event->modelClass === Role::class) {
            IdentityCacheService::flushRoleCaches();
        }

        if ($event->modelClass === Permission::class) {
            IdentityCacheService::flushPermissionCaches();
        }
    }

    /**
     * Handle Spatie role/permission attach/detach events.
     */
    protected function handleAccessControlChanged(object $event): void
    {
        if (! in_array(get_class($event), [
            RoleAttachedEvent::class,
            RoleDetachedEvent::class,
            PermissionAttachedEvent::class,
            PermissionDetachedEvent::class,
        ])) {
            return;
        }

        if (isset($event->model) && $event->model instanceof User) {
            IdentityCacheService::flushUserCache($event->model->id);
        } else {
            IdentityCacheService::flushUserCaches();
            IdentityCacheService::flushRoleCaches();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
