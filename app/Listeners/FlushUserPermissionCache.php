<?php

namespace App\Listeners;

use App\Models\User;

class FlushUserPermissionCache
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $model = $event->model;

        if ($model instanceof User) {
            $model->flushPermissionCache();
        } else {
            User::flushRedisTag();
        }
    }
}
