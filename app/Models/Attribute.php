<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends BaseModel
{
    use HasFactory, \Modules\Shared\Concerns\HasProtection;

    /**
     * Determine if the attribute is protected from deletion or modification.
     */
    public function shouldBeProtected(?User $user = null): bool
    {
        // Prevent deletion of attributes that have values used in variants
        return $this->values()->whereHas('variants')->exists();
    }

    protected $fillable = [
        'name',
        'code',
        'type',
        'is_filterable',
        'is_visible',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_filterable' => 'boolean',
            'is_visible' => 'boolean',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Attribute does not have is_active column, so we don't apply ActiveScope here.
    }

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function scopeFilterable(Builder $query): Builder
    {
        return $query->where('is_filterable', true);
    }
}
