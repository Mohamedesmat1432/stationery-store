<?php

namespace Modules\Shared\Repositories\Concerns;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\QueryBuilder;

trait HandlesQueryBuilder
{
    /**
     * Build the QueryBuilder instance for index pages.
     */
    protected function buildQueryBuilder(
        string|Builder $model,
        array $allowedFilters = [],
        array $allowedIncludes = [],
        array $allowedSorts = [],
        array|string $defaultSort = '-id',
        array $with = [],
        array $withCount = [],
        array $params = []
    ): QueryBuilder {
        if (! empty($params)) {
            request()->merge($params);
        }

        $query = QueryBuilder::for($model, request());

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
            ->defaultSort(...(array) $defaultSort);
    }

    /**
     * Apply common query builder logic and return paginated results.
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
        return $this->buildQueryBuilder(
            $model,
            $allowedFilters,
            $allowedIncludes,
            $allowedSorts,
            $defaultSort,
            $with,
            $withCount,
            $params
        )
            ->paginate($perPage)
            ->withQueryString();
    }
}
