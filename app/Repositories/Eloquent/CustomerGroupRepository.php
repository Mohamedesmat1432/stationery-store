<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomerGroup;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerGroupRepository extends BaseRepository implements CustomerGroupRepositoryInterface
{
    protected function getModelClass(): string
    {
        return CustomerGroup::class;
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return CustomerGroup::orderBy('sort_order')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function allActive(): Collection
    {
        return CustomerGroup::active()->orderBy('sort_order')->get();
    }
}
