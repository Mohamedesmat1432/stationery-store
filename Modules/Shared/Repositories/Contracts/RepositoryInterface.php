<?php

namespace Modules\Shared\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of \Illuminate\Database\Eloquent\Model
 */
interface RepositoryInterface
{
    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator;

    /**
     * @return TModel
     */
    public function findById(string $id): Model;

    /**
     * @return TModel
     */
    public function create(array $data): Model;

    /**
     * @return TModel
     */
    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function restore(Model $model): bool;

    public function forceDelete(Model $model): bool;

    public function bulkDelete(array $ids): bool;

    public function bulkRestore(array $ids): bool;

    public function bulkForceDelete(array $ids): bool;
}
