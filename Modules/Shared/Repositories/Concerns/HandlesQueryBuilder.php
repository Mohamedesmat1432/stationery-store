<?php

namespace Modules\Shared\Repositories\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;

trait HandlesQueryBuilder
{
    /**
     * Apply common query builder logic for index pages.
     */
    protected function applyQueryBuilder(
        string|Builder $model,
        array $allowedFilters = [],
        array $allowedIncludes = [],
        array $allowedSorts = [],
        array|string $defaultSort = '-id',
        int $perPage = 15,
        array $with = [],
        array $withCount = [],
        array $params = []
    ): LengthAwarePaginator {
        $query = QueryBuilder::for($model, request()->merge($params));

        if (! empty($with)) {
            $query->with($with);
        }

        if (! empty($withCount)) {
            $query->withCount($withCount);
        }

        return $query
            ->allowedFilters(...$allowedFilters)
            ->allowedIncludes(...$allowedIncludes)
            ->allowedSorts(...$allowedSorts)
            ->defaultSort($defaultSort)
            ->paginate($perPage)
            ->withQueryString();
    }
}
