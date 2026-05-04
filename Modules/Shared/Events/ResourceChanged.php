<?php

namespace Modules\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Unified event dispatched when any resource is changed (single or bulk).
 *
 * This replaces specific events like BulkOperationCompleted and allows
 * moving logic from model observers into services.
 */
class ResourceChanged
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  string  $modelClass  The class of the models affected.
     * @param  string  $action  The action performed (created, updated, deleted, restored, force_deleted, imported, etc.).
     * @param  array  $ids  The IDs of the affected records.
     * @param  array  $metadata  Optional additional information.
     */
    public function __construct(
        public string $modelClass,
        public string $action,
        public array $ids = [],
        public array $metadata = []
    ) {}
}
