<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'symbol',
        'rate',
        'is_active',
        'is_default',
        'decimal_places',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    public function convertTo(float $amount, Currency $targetCurrency): float
    {
        $baseAmount = $amount / $this->rate;
        return $baseAmount * $targetCurrency->rate;
    }
}
