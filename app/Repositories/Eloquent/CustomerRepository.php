<?php

namespace App\Repositories\Eloquent;

use App\Models\Customer;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class CustomerRepository extends BaseRepository implements CustomerRepositoryInterface
{
    protected function getModelClass(): string
    {
        return Customer::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return QueryBuilder::for(Customer::class)
            ->with(['user', 'group'])
            ->allowedFilters(...[
                AllowedFilter::scope('search'),
                AllowedFilter::exact('group', 'customer_group_id'),
                AllowedFilter::callback('trash', function ($query, $value) {
                    if ($value === 'only') {
                        $query->onlyTrashed();
                    } elseif ($value === 'with') {
                        $query->withTrashed();
                    }
                }),
            ])
            ->defaultSort('-id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): Customer
    {
        return Customer::with(['user', 'group', 'addresses'])->findOrFail($id);
    }

    public function getExportQuery(): Builder
    {
        return Customer::query()->with(['user', 'group']);
    }
}
