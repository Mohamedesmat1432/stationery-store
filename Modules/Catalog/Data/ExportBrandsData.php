<?php

namespace Modules\Catalog\Data;

use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ExportBrandsData extends Data
{
    public function __construct(
        /** @var array<int, string> */
        public array $columns,

        /** @var string */
        public string $format = 'xlsx',
    ) {}
}
