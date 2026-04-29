<?php

namespace App\Enums;

enum PriceType: string
{
    case BASE = 'base';
    case SALE = 'sale';
    case WHOLESALE = 'wholesale';
    case B2B = 'b2b';
}
