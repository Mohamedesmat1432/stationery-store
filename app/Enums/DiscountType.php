<?php

namespace App\Enums;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case BUY_X_GET_Y = 'buy_x_get_y';
    case FREE_SHIPPING = 'free_shipping';
}
