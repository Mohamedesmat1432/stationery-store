<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class OrderData extends Data
{
    public function __construct(
        public string $id,
        public string $order_number,
        public string $status,
        public float $grand_total,
        public string $created_at,
    ) {}
}
