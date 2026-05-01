<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class CustomersExport implements FromQuery, WithEvents, WithHeadings, WithMapping
{
    protected array $columns;

    protected Builder $query;

    public function __construct(Builder $query, array $columns = [])
    {
        $this->query = $query;
        $this->columns = empty($columns) ? ['name', 'email', 'group', 'total_spent', 'orders_count', 'created_at'] : $columns;
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
                'name' => __('Name'),
                'email' => __('Email'),
                'group' => __('Group'),
                'total_spent' => __('Total Spent'),
                'orders_count' => __('Orders Count'),
                'created_at' => __('Created At'),
                default => ucfirst(str_replace('_', ' ', $column)),
            };
        }

        return $headings;
    }

    public function map($customer): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            if ($column === 'name') {
                $row[] = $customer->user?->name;
            } elseif ($column === 'email') {
                $row[] = $customer->user?->email;
            } elseif ($column === 'group') {
                $row[] = $customer->group?->name;
            } elseif ($column === 'created_at') {
                $row[] = $customer->created_at ? $customer->created_at->format('Y-m-d H:i:s') : '';
            } else {
                $row[] = $customer->{$column};
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
