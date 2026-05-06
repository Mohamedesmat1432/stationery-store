<?php

namespace Modules\Catalog\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class ProductsExport implements FromQuery, WithEvents, WithHeadings, WithMapping
{
    protected array $columns;

    protected Builder $query;

    public function __construct(Builder $query, array $columns = [])
    {
        $this->query = $query;
        $this->columns = empty($columns) ? ['id', 'name', 'sku', 'price', 'is_active', 'created_at'] : $columns;
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
                'sku' => __('SKU'),
                'price' => __('Price'),
                'is_active' => __('Status'),
                'is_featured' => __('Featured'),
                'category_id' => __('Category'),
                'brand_id' => __('Brand'),
                'created_at' => __('Created At'),
                default => ucfirst(str_replace('_', ' ', $column)),
            };
        }

        return $headings;
    }

    public function map($product): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            if ($column === 'is_active') {
                $row[] = $product->is_active ? __('Active') : __('Draft');
            } elseif ($column === 'is_featured') {
                $row[] = $product->is_featured ? __('Yes') : __('No');
            } elseif ($column === 'price') {
                $row[] = (float) ($product->currentPrice()?->amount ?? 0);
            } elseif ($column === 'created_at') {
                $row[] = $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '';
            } elseif ($column === 'category_id') {
                $row[] = $product->category?->name ?? '-';
            } elseif ($column === 'brand_id') {
                $row[] = $product->brand?->name ?? '-';
            } else {
                $row[] = $product->{$column};
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
