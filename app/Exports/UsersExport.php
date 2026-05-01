<?php

namespace App\Exports;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class UsersExport implements FromQuery, WithEvents, WithHeadings, WithMapping
{
    protected array $columns;

    protected Builder $query;

    public function __construct(Builder $query, array $columns = [])
    {
        $this->query = $query;
        $this->columns = empty($columns) ? ['name', 'email', 'roles', 'created_at'] : $columns;
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
                'roles' => __('Roles'),
                'created_at' => __('Created At'),
                default => ucfirst(str_replace('_', ' ', $column)),
            };
        }

        return $headings;
    }

    public function map($user): array
    {
        $row = [];
        foreach ($this->columns as $column) {
            if ($column === 'roles') {
                $row[] = $user->roles->pluck('name')->map(function ($name) {
                    return ucfirst(str_replace('_', ' ', $name));
                })->join(', ');
            } elseif ($column === 'created_at') {
                $row[] = $user->created_at ? $user->created_at->format('Y-m-d H:i:s') : '';
            } else {
                $row[] = $user->{$column};
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
