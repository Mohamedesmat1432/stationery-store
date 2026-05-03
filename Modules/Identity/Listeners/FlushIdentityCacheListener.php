<?php

namespace Modules\Identity\Listeners;

use App\Models\Role;
use App\Models\User;
use Modules\Identity\Services\IdentityCacheService;
use Modules\Shared\Events\BulkOperationCompleted;

class FlushIdentityCacheListener
{
    /**
     * Handle the event.
     */
    public function handle(BulkOperationCompleted $event): void
    {
        if (in_array($event->modelClass, [User::class, Role::class])) {
            IdentityCacheService::flushUserCaches();
            IdentityCacheService::flushRoleCaches();
        }
    }
}
