<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

/**
 * User Model
 *
 * @property string $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string|null $avatar_url
 * @property string $locale
 * @property string $timezone
 * @property bool $is_active
 * @property Carbon|null $email_verified_at
 * @property Carbon|null $last_login_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'email', 'password', 'phone', 'avatar_url', 'locale', 'timezone', 'is_active'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements HasMedia
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    use HasRoles, HasUlids, InteractsWithMedia, LogsActivity, \Modules\Shared\Concerns\HasProtection, SoftDeletes;

    /**
     * Request-level cache for operator admin status to avoid redundant role checks.
     *
     * @var array<string, bool>
     */
    protected static array $operatorAdminCache = [];

    /**
     * Primary key is ULID string, not integer
     */
    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    // Removed booted() method, logic moved to UserObserver for better separation of concerns.

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function defaultWishlist(): ?Wishlist
    {
        return $this->wishlists()->where('is_default', true)->first();
    }

    /**
     * Determine if the user is an administrator.
     * Uses request-level caching for performance.
     */
    public function isAdmin(): bool
    {
        if (! isset(static::$operatorAdminCache[$this->id])) {
            static::$operatorAdminCache[$this->id] = $this->hasRole(Role::ROLE_ADMIN);
        }

        return static::$operatorAdminCache[$this->id];
    }

    /**
     * Determine if the user is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $operator = null): bool
    {
        if (! $operator) {
            return false;
        }

        // Users cannot delete themselves
        if ($operator->id === $this->id) {
            return true;
        }

        // Only admins can delete other admins
        if ($this->hasRole(Role::ROLE_ADMIN) && ! $operator->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include recently active users.
     */
    public function scopeRecentlyActive(Builder $query, int $days = 30): Builder
    {
        return $query->where('last_login_at', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to search users by name or email.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        });
    }
}
