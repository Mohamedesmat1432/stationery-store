<?php

namespace Modules\Catalog\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
class ImportProductsData extends Data
{
    public function __construct(
        public UploadedFile $file,
    ) {}
}
