<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class ProductData extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public string $sku,
        public string $slug,
        public ?string $description,
        public float $price,
        public bool $is_active,
        public ?string $featured_image,
    ) {}
}
