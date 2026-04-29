<?php

namespace App\Enums;

enum InventoryPolicy: string
{
    case DENY = 'deny';
    case CONTINUE = 'continue';
    case BACKORDER = 'backorder';
}
