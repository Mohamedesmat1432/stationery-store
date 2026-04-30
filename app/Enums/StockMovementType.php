<?php

namespace App\Enums;

enum StockMovementType: string
{
    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case ADJUSTMENT = 'adjustment';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case RETURN_FROM_CUSTOMER = 'return_from_customer';
    case RETURN_TO_VENDOR = 'return_to_vendor';
    case WASTE = 'waste';
    case DAMAGE = 'damage';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase',
            self::SALE => 'Sale',
            self::ADJUSTMENT => 'Adjustment',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
            self::RETURN_FROM_CUSTOMER => 'Return from Customer',
            self::RETURN_TO_VENDOR => 'Return to Vendor',
            self::WASTE => 'Waste',
            self::DAMAGE => 'Damage',
        };
    }

    public function sign(): int
    {
        return match ($this) {
            self::PURCHASE, self::TRANSFER_IN, self::RETURN_FROM_CUSTOMER => 1,
            self::SALE, self::TRANSFER_OUT, self::ADJUSTMENT, self::RETURN_TO_VENDOR, self::WASTE, self::DAMAGE => -1,
        };
    }
}
