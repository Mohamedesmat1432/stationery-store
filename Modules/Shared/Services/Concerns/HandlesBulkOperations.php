<?php

namespace Modules\Shared\Services\Concerns;

use Modules\Shared\Events\ResourceChanged;

/**
 * Trait HandlesBulkOperations
 *
 * Provides standardized bulk operation methods for services that utilize
 * a repository extending BaseRepository.
 */
trait HandlesBulkOperations
{
    /**
     * Bulk delete items by IDs.
     *
     * @param  array<int, string|int>  $ids
     */
    public function bulkDelete(array $ids): bool
    {
        $ids = $this->filterBulkIds($ids, 'delete');

        if (empty($ids)) {
            return false;
        }

        $result = $this->getRepository()->bulkDelete($ids);
        if ($result) {
            event(new ResourceChanged($this->getModelClass(), 'bulk_deleted', $ids));
        }

        return $result;
    }

    /**
     * Bulk restore items by IDs.
     *
     * @param  array<int, string|int>  $ids
     */
    public function bulkRestore(array $ids): bool
    {
        $ids = $this->filterBulkIds($ids, 'restore');

        if (empty($ids)) {
            return false;
        }

        $result = $this->getRepository()->bulkRestore($ids);
        if ($result) {
            event(new ResourceChanged($this->getModelClass(), 'bulk_restored', $ids));
        }

        return $result;
    }

    /**
     * Bulk force delete items by IDs.
     *
     * @param  array<int, string|int>  $ids
     */
    public function bulkForceDelete(array $ids): bool
    {
        $ids = $this->filterBulkIds($ids, 'forceDelete');

        if (empty($ids)) {
            return false;
        }

        $result = $this->getRepository()->bulkForceDelete($ids);
        if ($result) {
            event(new ResourceChanged($this->getModelClass(), 'bulk_force_deleted', $ids));
        }

        return $result;
    }

    /**
     * Filter IDs before bulk operation.
     * Services can override this to implement custom protection/filtering logic.
     *
     * @param  array<int, string|int>  $ids
     * @return array<int, string|int>
     */
    protected function filterBulkIds(array $ids, string $action): array
    {
        return $ids;
    }

    /**
     * Get the model class for the event.
     */
    abstract protected function getModelClass(): string;

    /**
     * Helper to get the repository instance.
     * Services using this trait must provide this method.
     */
    abstract protected function getRepository();
}
