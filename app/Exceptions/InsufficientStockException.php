<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly string $productName,
        public readonly float $requested,
        public readonly float $available,
    ) {
        parent::__construct(
            "Insufficient stock for '{$productName}': requested {$requested}, available {$available}."
        );
    }
}
