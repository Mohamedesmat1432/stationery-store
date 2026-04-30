<?php

namespace App\Data;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class ProductData extends Data
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $sku,
        public ?string $slug,
        public ?string $description,
        public float $price,
        public bool $is_active,
        public ?string $category_id = null,
        public ?string $brand_id = null,
        public ?string $featured_image = null,
        public ?string $deleted_at = null,
    ) {}

    public static function fromModel(Product $product): self
    {
        return new self(
            $product->id,
            $product->name,
            $product->sku,
            $product->slug,
            $product->description,
            (float) $product->price,
            (bool) $product->is_active,
            $product->category_id,
            $product->brand_id,
            $product->featured_image,
            $product->deleted_at?->toDateTimeString(),
        );
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $productId = request()->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'description' => ['nullable', 'string'],
            'featured_image' => ['nullable', 'string'],
        ];
    }
}
