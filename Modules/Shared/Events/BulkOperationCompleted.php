<?php

namespace Modules\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulkOperationCompleted
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  string  $modelClass  The class of the models affected.
     * @param  string  $action  The action performed (delete, restore, forceDelete, import).
     * @param  array  $ids  The IDs of the affected records.
     */
    public function __construct(
        public string $modelClass,
        public string $action,
        public array $ids = []
    ) {}
}
