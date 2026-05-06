<?php

namespace Modules\Catalog\Data;

use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class CategoryData extends Data
{
    #[Computed]
    public ?string $full_path;

    #[Computed]
    public ?string $parent_name;

    #[Computed]
    public ?array $breadcrumbs;

    #[Computed]
    public int $total_product_count;

    /** @var array<self>|null */
    #[Computed]
    public ?array $children;

    public function __construct(
        /** @var string|null */
        public ?string $id,

        /** @var string */
        public string $name,

        /** @var string */
        public string $slug,

        /** @var string|null */
        public ?string $description = null,

        /** @var string|null */
        public ?string $parent_id = null,

        /** @var bool */
        public bool $is_active = true,

        /** @var bool */
        public bool $is_featured = false,

        /** @var int */
        public int $sort_order = 0,

        /** @var mixed */
        public mixed $icon = null,

        /** @var mixed */
        public mixed $banner_image = null,

        /** @var string|null */
        public ?string $meta_title = null,

        /** @var string|null */
        public ?string $meta_description = null,

        /** @var string|null */
        public ?string $meta_keywords = null,

        /** @var string|null */
        public ?string $deleted_at = null,
    ) {
        $this->full_path = null;
        $this->breadcrumbs = null;
        $this->total_product_count = 0;
        $this->children = null;
    }

    /**
     * Create a DTO instance from a Category model.
     */
    public static function fromCategory(Category $category): self
    {
        $data = new self(
            id: $category->id,
            name: $category->name,
            slug: $category->slug,
            description: $category->description,
            parent_id: $category->parent_id,
            is_active: $category->is_active,
            is_featured: $category->is_featured,
            sort_order: $category->sort_order,
            icon: $category->getFirstMediaUrl('icon'),
            banner_image: $category->getFirstMediaUrl('banner'),
            meta_title: $category->meta_title,
            meta_description: $category->meta_description,
            meta_keywords: $category->meta_keywords,
            deleted_at: $category->deleted_at?->toIso8601String(),
        );

        if ($category->relationLoaded('parent')) {
            $data->full_path = $category->full_path;
            $data->parent_name = $category->parent?->name;
            $data->breadcrumbs = $category->getBreadcrumbs();
        }

        if ($category->relationLoaded('allChildren')) {
            $data->total_product_count = $category->total_product_count;
            $data->children = self::collect($category->allChildren)->toArray();
        } elseif ($category->relationLoaded('children')) {
            $data->children = self::collect($category->children)->toArray();
        } else {
            $data->children = null;
        }

        return $data;
    }

    public static function rules(?ValidationContext $context = null): array
    {
        $category = request()->route('category');
        $categoryId = ($category instanceof Category ? $category->id : $category) ?? request()->input('id');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', Rule::exists('categories', 'id')],
            'is_active' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer'],
            'icon' => ['nullable', 'sometimes', 'max:2048', fn ($attr, $val, $fail) => $val instanceof UploadedFile || is_string($val) || $fail(__('The :attribute must be an image or a valid URL.'))],
            'banner_image' => ['nullable', 'sometimes', 'max:5120', fn ($attr, $val, $fail) => $val instanceof UploadedFile || is_string($val) || $fail(__('The :attribute must be an image or a valid URL.'))],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
        ];
    }
}
