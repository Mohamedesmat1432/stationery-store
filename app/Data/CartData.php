<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class CartData extends Data
{
    public function __construct(
        public string $id,
        public float $subtotal,
        public float $grand_total,
        public int $items_count,
    ) {}
}
