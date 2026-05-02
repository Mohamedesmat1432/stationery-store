<?php

namespace Modules\Identity\Data;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class ImportUsersData extends Data
{
    public function __construct(
        public UploadedFile $file,
    ) {}

    public static function rules(?ValidationContext $context = null): array
    {
        return [
            'file' => ['required', 'file', 'mimes:xlsx,csv', 'max:10240'],
        ];
    }
    public static function attributes(...$args): array
    {
        return [
            'file' => __('File'),
        ];
    }
}
