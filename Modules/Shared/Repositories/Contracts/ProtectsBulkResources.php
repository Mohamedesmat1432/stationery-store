<?php

namespace Modules\Shared\Repositories\Contracts;

interface ProtectsBulkResources
{
    /**
     * Get IDs that are protected from deletion or modification from a given set of IDs.
     *
     * @param  array<string>  $ids
     * @return array<string>
     */
    public function getProtectedIds(array $ids): array;
}
