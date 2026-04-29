<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case RETURN = 'return';

    public function label(): string
    {
        return match($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
            self::RETURN => 'Return',
        };
    }

    public function sign(): int
    {
        return match($this) {
            self::PURCHASE, self::TRANSFER_IN, self::RETURN => 1,
            self::SALE, self::TRANSFER_OUT, self::ADJUSTMENT => -1,
        };
    }
}
