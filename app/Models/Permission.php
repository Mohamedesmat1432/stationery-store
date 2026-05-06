<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasUlids, \Modules\Shared\Concerns\HasProtection;

    /**
     * Primary key is ULID string, not integer
     */
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Determine if the permission is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        // For now, all system permissions are protected from manual deletion via UI
        return true;
    }
}
