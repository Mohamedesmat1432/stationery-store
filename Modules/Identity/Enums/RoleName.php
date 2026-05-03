<?php

namespace Modules\Identity\Enums;

enum RoleName: string
{
    case ADMIN = 'admin';
    case CUSTOMER = 'customer';
    case MANAGER = 'manager';
    case EDITOR = 'editor';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => __('Admin'),
            self::CUSTOMER => __('Customer'),
            self::MANAGER => __('Manager'),
            self::EDITOR => __('Editor'),
        };
    }
}
