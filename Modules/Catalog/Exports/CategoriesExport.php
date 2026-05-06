<?php

namespace Modules\Catalog\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class CategoriesExport implements FromQuery, WithEvents, WithHeadings, WithMapping
{
    protected array $columns;

    protected Builder $query;

    public function __construct(Builder $query, array $columns = [])
    {
        $this->query = $query;
        $this->columns = empty($columns) ? ['id', 'name', 'slug', 'parent_id', 'is_active', 'created_at'] : $columns;
    }

    public function query()
    {
        return $this->query;
    }

    public function headings(): array
    {
        $headings = [];
        foreach ($this->columns as $column) {
            $headings[] = match ($column) {
                'id' => __('ID'),
                'name' => __('Name'),
                'slug' => __('Slug'),
                'parent_id' => __('Parent ID'),
                'is_active' => __('Status'),
                'created_at' => __('Created At'),
                default => ucfirst(str_replace('_', ' ', $column)),
            };
        }

        return $headings;
    }

    public function map($category): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            if ($column === 'is_active') {
                $row[] = $category->is_active ? __('Active') : __('Inactive');
            } elseif ($column === 'created_at') {
                $row[] = $category->created_at ? $category->created_at->format('Y-m-d H:i:s') : '';
            } else {
                $row[] = $category->{$column};
            }
        }

        return $row;
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
