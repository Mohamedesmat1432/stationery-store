<?php

namespace App\Providers;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Optional;
use Spatie\LaravelTypeScriptTransformer\LaravelData\Transformers\DataClassTransformer;
use Spatie\LaravelTypeScriptTransformer\TypeScriptTransformerApplicationServiceProvider as BaseTypeScriptTransformerServiceProvider;
use Spatie\TypeScriptTransformer\Formatters\PrettierFormatter;
use Spatie\TypeScriptTransformer\Transformers\AttributedClassTransformer;
use Spatie\TypeScriptTransformer\Transformers\EnumTransformer;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfigFactory;
use Spatie\TypeScriptTransformer\Writers\GlobalNamespaceWriter;

class TypeScriptTransformerServiceProvider extends BaseTypeScriptTransformerServiceProvider
{
    protected function configure(TypeScriptTransformerConfigFactory $config): void
    {
        $config
            ->transformer(AttributedClassTransformer::class)
            ->transformer(EnumTransformer::class)
            ->transformer(DataClassTransformer::class)
            ->transformDirectories(...$this->getTransformDirectories())
            ->replaceType(UploadedFile::class, 'string')
            ->replaceType(Optional::class, 'any')
            ->outputDirectory(resource_path('js/types'))
            ->writer(new GlobalNamespaceWriter('generated.d.ts'))
            ->formatter(PrettierFormatter::class);
    }

    /**
     * Get the directories that should be scanned for TypeScript transformation.
     */
    protected function getTransformDirectories(): array
    {
        return array_filter(array_merge(
            [app_path()],
            glob(base_path('Modules/*/Data')),
            glob(base_path('Modules/*/Enums'))
        ), 'is_dir');
    }
}
