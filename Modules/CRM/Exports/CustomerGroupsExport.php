<?php

namespace Modules\CRM\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomerGroupsExport implements FromQuery, WithEvents, WithHeadings, WithMapping
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
                'name' => __('Name'),
                'slug' => __('Slug'),
                'discount_percentage' => __('Discount Percentage'),
                'is_active' => __('Status'),
                'created_at' => __('Created At'),
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
                'is_active' => $group->is_active ? __('Active') : __('Inactive'),
                'created_at' => $group->created_at->toDateTimeString(),
                default => $group->{$column},
            };
        }

        return $mapped;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                if (App::getLocale() === 'ar') {
                    $event->sheet->getDelegate()->setRightToLeft(true);
                }
            },
        ];
    }
}
