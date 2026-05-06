<?php

namespace Modules\Catalog\Data;

use App\Models\Product;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ProductData extends Data
{
    public function __construct(
        public ?string $id,
        public string $name,
        public string $sku,
        public ?string $slug,
        public ?string $description,
        public ?string $short_description,
        public float $price,
        public ?float $compare_at_price,
        public ?float $cost_price,
        public bool $is_active,
        public bool $is_featured,
        public bool $is_digital = false,
        public bool $is_taxable = true,
        public ?string $barcode = null,
        public ?float $weight = null,
        public ?array $dimensions = null,
        public string $inventory_policy = 'deny',
        public ?string $category_id = null,
        public ?string $brand_id = null,
        public ?string $meta_title = null,
        public ?string $meta_description = null,
        public ?string $meta_keywords = null,

        #[Computed]
        public ?string $featured_image = null,

        #[Computed]
        public ?string $deleted_at = null,

        #[Computed]
        public ?CategoryData $category = null,

        #[Computed]
        public ?BrandData $brand = null,
    ) {}

    public static function fromProduct(Product $product): self
    {
        $currentPrice = $product->currentPrice();

        $data = new self(
            $product->id,
            $product->name,
            $product->sku,
            $product->slug,
            $product->description,
            $product->short_description,
            (float) ($currentPrice?->amount ?? 0),
            $currentPrice?->compare_at_price ? (float) $currentPrice->compare_at_price : null,
            $currentPrice?->cost_price ? (float) $currentPrice->cost_price : null,
            (bool) $product->is_active,
            (bool) $product->is_featured,
            (bool) $product->is_digital,
            (bool) $product->is_taxable,
            $product->barcode,
            $product->weight ? (float) $product->weight : null,
            $product->dimensions,
            $product->inventory_policy->value ?? 'deny',
            $product->category_id,
            $product->brand_id,
            $product->meta_title,
            $product->meta_description,
            $product->meta_keywords,
            $product->getFeaturedImageUrl(),
            $product->deleted_at?->toIso8601String(),
        );

        if ($product->relationLoaded('category') && $product->category) {
            $data->category = CategoryData::fromCategory($product->category);
        }

        if ($product->relationLoaded('brand') && $product->brand) {
            $data->brand = BrandData::fromBrand($product->brand);
        }

        return $data;
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $productId = request()->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($productId)],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($productId)],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'is_digital' => ['boolean'],
            'is_taxable' => ['boolean'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'dimensions' => ['nullable', 'array'],
            'inventory_policy' => ['required', 'string', 'in:deny,continue,backorder'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'description' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'featured_image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
