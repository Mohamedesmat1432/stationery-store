<?php

namespace Modules\Catalog\Imports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoriesImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        // Map headings to support both English and Arabic slugified keys
        $mappedRows = $rows->map(function ($row) {
            return [
                'name' => $row['name'] ?? $row['alasm'] ?? $row['الاسم'] ?? null,
                'slug' => $row['slug'] ?? $row['alrabt_alitaif'] ?? $row['الرابط_اللطيف'] ?? null,
                'description' => $row['description'] ?? $row['alwsf'] ?? $row['الوصف'] ?? null,
                'parent_id' => $row['parent_id'] ?? $row['mqm_alfiy_alaisasiy'] ?? $row['رقم_الفئة_الأساسية'] ?? null,
                'status' => $row['status'] ?? $row['alhalt'] ?? $row['الحالة'] ?? 'Active',
                'featured' => $row['featured'] ?? $row['mmiz'] ?? $row['مميزة'] ?? 'No',
                'sort_order' => $row['sort_order'] ?? $row['trtib_altrd'] ?? $row['ترتيب_العرض'] ?? 0,
            ];
        });

        DB::transaction(function () use ($mappedRows) {
            foreach ($mappedRows as $row) {
                if (empty($row['name'])) {
                    continue;
                }

                Category::create([
                    'name' => $row['name'],
                    'slug' => $row['slug'] ?? Str::slug($row['name']),
                    'description' => $row['description'],
                    'parent_id' => $row['parent_id'],
                    'is_active' => in_array($row['status'], ['Active', 'نشط', '1', 1]),
                    'is_featured' => in_array($row['featured'], ['Yes', 'نعم', '1', 1]),
                    'sort_order' => (int) $row['sort_order'],
                ]);
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'parent_id' => ['nullable', 'exists:categories,id'],
        ];
    }

    public function customValidationAttributes(): array
    {
        return [
            'name' => __('Name'),
            'slug' => __('Slug'),
            'parent_id' => __('Parent Category'),
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
