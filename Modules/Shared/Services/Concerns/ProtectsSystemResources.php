<?php

namespace Modules\Shared\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Repositories\Contracts\ProtectsBulkResources;

trait ProtectsSystemResources
{
    /**
     * Check if a resource is protected from deletion or modification.
     */
    abstract public function isProtected(Model $model): bool;

    /**
     * Filter out protected IDs from a bulk operation.
     */
    protected function filterBulkIds(array $ids, string $action): array
    {
        if ($action === 'restore') {
            return $ids;
        }

        $repository = $this->getRepository();

        if ($repository instanceof ProtectsBulkResources) {
            $protectedIds = $repository->getProtectedIds($ids);
        } else {
            // Fallback for repositories not implementing the interface yet
            $modelClass = $this->getModelClass();
            $protectedIds = $modelClass::whereIn('id', $ids)
                ->get()
                ->filter(fn (Model $model) => $this->isProtected($model))
                ->pluck('id')
                ->toArray();
        }

        return array_diff($ids, $protectedIds);
    }

    /**
     * Get the model class for the service.
     */
    abstract protected function getModelClass(): string;

    /**
     * Get the repository for the service.
     */
    abstract protected function getRepository();
}
