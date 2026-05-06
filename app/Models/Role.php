<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Support\Carbon;
use Modules\Identity\Enums\RoleName;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Role Model
 *
 * @property string $id
 * @property string $name
 * @property string $guard_name
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Role extends SpatieRole
{
    use HasUlids, \Modules\Shared\Concerns\HasProtection;

    public const ROLE_ADMIN = RoleName::ADMIN->value;

    /**
     * Primary key is ULID string, not integer
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Determine if the role is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        return $this->name === self::ROLE_ADMIN;
    }

    /**
     * Scope a query to search roles.
     */
    public function scopeSearch($query, ?string $search)
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        });
    }
}
