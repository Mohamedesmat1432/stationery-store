<?php

namespace App\Data\CRM;

use Illuminate\Http\UploadedFile;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Support\Validation\ValidationContext;

class ImportCustomersData extends Data
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
}
