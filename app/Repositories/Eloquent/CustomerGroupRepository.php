<?php

namespace App\Repositories\Eloquent;

use App\Models\CustomerGroup;
use App\Repositories\Contracts\CustomerGroupRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CustomerGroupRepository implements CustomerGroupRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return CustomerGroup::orderBy('sort_order')->orderBy('id', 'desc')->paginate($perPage);
    }

    public function findById(string $id): CustomerGroup
    {
        return CustomerGroup::findOrFail($id);
    }

    public function create(array $data): CustomerGroup
    {
        return CustomerGroup::create($data);
    }

    public function update(CustomerGroup $group, array $data): CustomerGroup
    {
        $group->update($data);

        return $group;
    }

    public function delete(CustomerGroup $group): bool
    {
        return $group->delete();
    }

    public function restore(CustomerGroup $group): bool
    {
        return $group->restore();
    }

    public function forceDelete(CustomerGroup $group): bool
    {
        return $group->forceDelete();
    }

    public function bulkDelete(array $ids): bool
    {
        return CustomerGroup::whereIn('id', $ids)
            ->get()
            ->each(fn (CustomerGroup $group) => $group->delete())
            ->isNotEmpty();
    }

    public function bulkRestore(array $ids): bool
    {
        return CustomerGroup::onlyTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (CustomerGroup $group) => $group->restore())
            ->isNotEmpty();
    }

    public function bulkForceDelete(array $ids): bool
    {
        return CustomerGroup::onlyTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (CustomerGroup $group) => $group->forceDelete())
            ->isNotEmpty();
    }

    public function allActive(): Collection
    {
        return CustomerGroup::active()->orderBy('sort_order')->get();
    }
}
