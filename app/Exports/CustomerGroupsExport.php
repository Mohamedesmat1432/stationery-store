<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerGroupsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        protected Builder $query,
        protected array $columns
    ) {}

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->columns as $column) {
            $headings[] = match ($column) {
                'name' => 'Name',
                'slug' => 'Slug',
                'discount_percentage' => 'Discount %',
                'is_active' => 'Status',
                'created_at' => 'Created At',
                default => ucfirst(str_replace('_', ' ', $column)),
            };
        }

        return $headings;
    }

    public function map($group): array
    {
        $mapped = [];
        foreach ($this->columns as $column) {
            $mapped[] = match ($column) {
                'is_active' => $group->is_active ? 'Active' : 'Inactive',
                'created_at' => $group->created_at->toDateTimeString(),
                default => $group->{$column},
            };
        }

        return $mapped;
    }
}
