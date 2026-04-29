<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'countries',
        'states',
        'postal_codes',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'countries' => 'array',
            'states' => 'array',
            'postal_codes' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'zone_id');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCountry(Builder $query, string $countryCode)
    {
        return $query->whereJsonContains('countries', $countryCode);
    }

    public function scopeForState(Builder $query, string $stateCode)
    {
        return $query->whereJsonContains('states', $stateCode);
    }

    public function includesCountry(string $countryCode): bool
    {
        return in_array($countryCode, $this->countries ?? []);
    }
}
