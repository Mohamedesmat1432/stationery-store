<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerGroup extends BaseModel
{
    use HasFactory, LogsActivity, \Modules\Shared\Concerns\HasProtection;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'discount_percentage', 'is_active'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected $fillable = [
        'name',
        'slug',
        'description',
        'discount_percentage',
        'is_active',
        'sort_order',
    ];

    /**
     * Protected slugs that cannot be deleted or modified.
     */
    protected const PROTECTED_SLUGS = ['retail', 'general'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        parent::booted();

        static::saving(function (self $model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('slug', 'like', "%{$search}%");
        });
    }

    /**
     * Determine if the customer group is protected from deletion/modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        return in_array($this->slug, self::PROTECTED_SLUGS, true);
    }
}
