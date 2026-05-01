<?php

namespace App\Services\Concerns;

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
     */
    public function bulkDelete(array $ids): bool
    {
        return $this->getRepository()->bulkDelete($ids);
    }

    /**
     * Bulk restore items by IDs.
     */
    public function bulkRestore(array $ids): bool
    {
        return $this->getRepository()->bulkRestore($ids);
    }

    /**
     * Bulk force delete items by IDs.
     */
    public function bulkForceDelete(array $ids): bool
    {
        return $this->getRepository()->bulkForceDelete($ids);
    }

    /**
     * Helper to get the repository instance.
     * Services using this trait must provide a getRepository() method or
     * have a property named after the primary repository.
     */
    protected function getRepository()
    {
        if (method_exists($this, 'repository')) {
            return $this->repository();
        }

        // Common pattern: property name matches service name (e.g. $userRepository in UserService)
        $reflect = new \ReflectionClass($this);
        $serviceName = str_replace('Service', '', $reflect->getShortName());
        $propName = lcfirst($serviceName).'Repository';

        if (property_exists($this, $propName)) {
            return $this->{$propName};
        }

        throw new \RuntimeException('Repository not found in '.get_class($this));
    }
}
