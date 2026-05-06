<?php

namespace Modules\Catalog\Data;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ExportCategoriesData extends Data
{
    public function __construct(
        /** @var array<int, string> */
        public array $columns,

        /** @var string */
        public string $format,
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['string', Rule::in(['id', 'name', 'slug', 'parent_id', 'is_active', 'is_featured', 'created_at'])],
            'format' => ['required', 'string', Rule::in(['xlsx', 'csv'])],
        ];
    }
}
