<?php

namespace Modules\Catalog\Imports;

use App\Models\Product;
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

class ProductsImport implements SkipsOnError, SkipsOnFailure, ToCollection, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors, SkipsFailures;

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                if (empty($row['name']) || empty($row['sku'])) {
                    continue;
                }

                $product = Product::create([
                    'name' => $row['name'],
                    'sku' => $row['sku'],
                    'slug' => $row['slug'] ?? Str::slug($row['name']),
                    'description' => $row['description'] ?? null,
                    'short_description' => $row['short_description'] ?? null,
                    'category_id' => $row['category_id'] ?? null,
                    'brand_id' => $row['brand_id'] ?? null,
                    'is_active' => in_array($row['status'] ?? 'Active', ['Active', 'نشط', '1', 1]),
                    'is_featured' => in_array($row['featured'] ?? 'No', ['Yes', 'نعم', '1', 1]),
                    'barcode' => $row['barcode'] ?? null,
                    'weight' => $row['weight'] ?? null,
                    'inventory_policy' => $row['inventory_policy'] ?? 'deny',
                ]);

                // Create base price
                if (isset($row['price'])) {
                    $product->prices()->create([
                        'amount' => $row['price'],
                        'currency_id' => config('app.currency_id'),
                        'type' => 'base',
                    ]);
                }
            }
        });
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
