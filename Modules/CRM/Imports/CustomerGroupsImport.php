<?php

namespace Modules\CRM\Imports;

use App\Models\CustomerGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerGroupsImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        // Map headings to support both English and Arabic slugified keys
        $mappedRows = $rows->map(function ($row) {
            return [
                'name' => $row['name'] ?? $row['alasm'] ?? $row['الاسم'] ?? null,
                'slug' => $row['slug'] ?? $row['almrf_alltyf'] ?? $row['المعرف_اللطيف'] ?? null,
                'discount_percentage' => $row['discount_percentage'] ?? $row['nsba_alkhsm'] ?? $row['نسبة_الخصم'] ?? 0,
                'is_active' => $row['is_active'] ?? $row['alhala'] ?? $row['الحالة'] ?? true,
            ];
        });

        DB::transaction(function () use ($mappedRows) {
            foreach ($mappedRows as $row) {
                if (empty($row['name']) || empty($row['slug'])) {
                    continue;
                }

                CustomerGroup::updateOrCreate(
                    ['slug' => $row['slug']],
                    [
                        'name' => $row['name'],
                        'discount_percentage' => (float) $row['discount_percentage'],
                        'is_active' => filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN),
                    ]
                );
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'name' => __('Name'),
            'slug' => __('Slug'),
            'discount_percentage' => __('Discount Percentage'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
