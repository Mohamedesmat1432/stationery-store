<?php

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingMethod extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'carrier',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new ActiveScope);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'method_id');
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function scopeForCarrier(Builder $query, string $carrier)
    {
        return $query->where('carrier', $carrier);
    }
}
