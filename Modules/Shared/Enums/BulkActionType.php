<?php

namespace Modules\Shared\Enums;

enum BulkActionType: string
{
    case DELETE = 'delete';
    case RESTORE = 'restore';
    case FORCE_DELETE = 'forceDelete';

    public function label(): string
    {
        return match ($this) {
            self::DELETE => 'delete',
            self::RESTORE => 'restore',
            self::FORCE_DELETE => 'permanently delete',
        };
    }
}
