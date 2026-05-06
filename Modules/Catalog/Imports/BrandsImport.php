<?php

namespace Modules\Catalog\Imports;

use App\Models\Brand;
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

class BrandsImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (empty($row['name'])) {
                    continue;
                }

                Brand::create([
                    'name' => $row['name'],
                    'slug' => $row['slug'] ?? Str::slug($row['name']),
                    'description' => $row['description'] ?? null,
                    'website' => $row['website'] ?? null,
                    'is_active' => in_array($row['status'] ?? 'Active', ['Active', 'نشط', '1', 1]),
                    'sort_order' => (int) ($row['sort_order'] ?? 0),
                ]);
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:brands,slug'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
