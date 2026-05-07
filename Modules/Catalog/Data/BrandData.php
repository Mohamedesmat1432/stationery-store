<?php

namespace Modules\Catalog\Data;

use App\Models\Brand;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class BrandData extends Data
{
    #[Computed]
    public bool $is_protected;

    public function __construct(
        public ?string $id,
        public string $name,
        public string $slug,
        public ?string $description = null,
        public ?string $website = null,
        public bool $is_active = true,
        public int $sort_order = 0,
        public mixed $logo = null,

        #[Computed]
        public ?string $logo_url = null,

        #[Computed]
        public ?string $deleted_at = null,

        #[Computed]
        public ?int $products_count = null,
    ) {}

    public static function fromBrand(Brand $brand): self
    {
        $data = new self(
            id: $brand->id,
            name: $brand->name,
            slug: $brand->slug,
            description: $brand->description,
            website: $brand->website,
            is_active: (bool) $brand->is_active,
            sort_order: (int) $brand->sort_order,
            logo: null,
            logo_url: $brand->getFirstMediaUrl('logo'),
            deleted_at: $brand->deleted_at?->toIso8601String(),
            products_count: $brand->products_count,
        );

        $data->is_protected = $brand->isProtected();

        return $data;
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $brand = request()->route('brand');
        $brandId = ($brand instanceof Brand ? $brand->id : $brand) ?? request()->input('id');

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug' => ['required', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brandId)],
            'description' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'is_active' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'logo' => ['nullable', 'sometimes', 'image', 'max:2048'],
        ];
    }
}
