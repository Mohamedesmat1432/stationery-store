<?php

namespace Modules\Shared\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Shared\Repositories\Contracts\RepositoryInterface;

abstract class BaseRepository implements RepositoryInterface
{
    protected string $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    abstract protected function getModelClass(): string;

    public function paginate(int $perPage = 15, array $params = []): LengthAwarePaginator
    {
        return $this->model::orderBy('id', 'desc')->paginate($perPage);
    }

    /**
     * @note Subclasses should override this method to apply filters, sorting,
     *       and eager loading via applyQueryBuilder() or custom logic.
     */
    public function findById(string|int $id): Model
    {
        return $this->model::findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model::create($data);
    }

    public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model;
    }

    public function delete(Model $model): bool
    {
        return $model->delete();
    }

    public function restore(Model $model): bool
    {
        return $model->restore();
    }

    public function forceDelete(Model $model): bool
    {
        return $model->forceDelete();
    }

    public function bulkDelete(array $ids): bool
    {
        return $this->performBulkAction($ids, 'delete');
    }

    public function bulkRestore(array $ids): bool
    {
        return $this->performBulkAction($ids, 'restore', true);
    }

    public function bulkForceDelete(array $ids): bool
    {
        return $this->performBulkAction($ids, 'forceDelete', true);
    }

    /**
     * Perform a bulk update on models.
     */
    public function bulkUpdate(array $ids, array $data): bool
    {
        if (empty($ids) || empty($data)) {
            return false;
        }

        return DB::transaction(function () use ($ids, $data) {
            $this->model::whereIn('id', $ids)->lazy()->each->update($data);

            return true;
        });
    }

    /**
     * Perform a bulk action on models while ensuring model events are triggered.
     * Uses lazy() for memory efficiency with large datasets.
     */
    protected function performBulkAction(array $ids, string $action, bool $onlyTrashed = false): bool
    {
        if (empty($ids)) {
            return false;
        }

        return DB::transaction(function () use ($ids, $action, $onlyTrashed) {
            $query = $this->model::whereIn('id', $ids);

            if ($onlyTrashed) {
                $query->onlyTrashed();
            }

            $query->lazy()->each->{$action}();

            return true;
        });
    }

    /**
     * Toggle the active status of a model.
     */
    public function toggleActive(Model $model): bool
    {
        if (! isset($model->is_active)) {
            return false;
        }

        $model->is_active = ! $model->is_active;

        return $model->save();
    }
}
