<?php

namespace App\Data\CRM;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class ExportCustomersData extends Data
{
    public function __construct(
        /** @var array<string> */
        public array $columns,
        public string $format,
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['string', Rule::in(['name', 'email', 'group', 'total_spent', 'orders_count', 'created_at'])],
            'format' => ['required', 'string', Rule::in(['xlsx', 'csv'])],
        ];
    }
}
