<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

abstract class BaseRepository implements RepositoryInterface
{
    protected string $model;

    public function __construct()
    {
        $this->model = $this->getModelClass();
    }

    abstract protected function getModelClass(): string;

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model::orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): Model
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
        return DB::transaction(
            fn () => $this->model::whereIn('id', $ids)
                ->get()
                ->each(fn (Model $model) => $model->delete())
                ->isNotEmpty()
        );
    }

    public function bulkRestore(array $ids): bool
    {
        return DB::transaction(
            fn () => $this->model::onlyTrashed()
                ->whereIn('id', $ids)
                ->get()
                ->each(fn (Model $model) => $model->restore())
                ->isNotEmpty()
        );
    }

    public function bulkForceDelete(array $ids): bool
    {
        return DB::transaction(
            fn () => $this->model::onlyTrashed()
                ->whereIn('id', $ids)
                ->get()
                ->each(fn (Model $model) => $model->forceDelete())
                ->isNotEmpty()
        );
    }
}
