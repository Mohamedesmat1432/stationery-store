<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tax extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'rate',
        'type',
        'zone_type',
        'zone_codes',
        'priority',
        'is_compound',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:4',
            'zone_codes' => 'array',
            'priority' => 'integer',
            'is_compound' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_tax');
    }

    public function scopeForZone(Builder $query, string $countryCode, ?string $stateCode = null)
    {
        return $query->where(function ($q) use ($countryCode, $stateCode) {
            $q->where('zone_type', 'global')
                ->orWhere(function ($sq) use ($countryCode) {
                    $sq->where('zone_type', 'country')
                        ->whereJsonContains('zone_codes', $countryCode);
                })
                ->orWhere(function ($sq) use ($stateCode) {
                    if ($stateCode) {
                        $sq->where('zone_type', 'state')
                            ->whereJsonContains('zone_codes', $stateCode);
                    }
                });
        });
    }

    public function scopeOrdered(Builder $query)
    {
        return $query->orderBy('priority', 'asc');
    }

    public function calculate(float $amount): float
    {
        if ($this->type === 'percentage') {
            return round($amount * ($this->rate / 100), 4);
        }

        return (float) $this->rate;
    }

    public function appliesToCountry(string $countryCode): bool
    {
        if ($this->zone_type === 'global') {
            return true;
        }
        if ($this->zone_type === 'country') {
            return in_array($countryCode, $this->zone_codes ?? []);
        }

        return false;
    }
}
