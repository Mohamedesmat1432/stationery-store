<?php

namespace App\Enums;

enum FulfillmentStatus: string
{
    case UNFULFILLED = 'unfulfilled';
    case PARTIAL = 'partial';
    case FULFILLED = 'fulfilled';

    public function label(): string
    {
        return match($this) {
            self::UNFULFILLED => 'Unfulfilled',
            self::PARTIAL => 'Partial',
            self::FULFILLED => 'Fulfilled',
        };
    }
}