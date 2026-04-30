<?php

/* [NEW] ShipmentStatus.php */

namespace App\Enums;

enum ShipmentStatus: string
{
    case PENDING = 'pending';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case RETURNED = 'returned';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::RETURNED => 'Returned',
            self::CANCELLED => 'Cancelled',
        };
    }
}
