<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasUlids;

    /**
     * Primary key is ULID string, not integer
     */
    public $incrementing = false;

    protected $keyType = 'string';
}
