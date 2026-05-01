<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'zone_id',
        'method_id',
        'min_weight',
        'max_weight',
        'min_order_amount',
        'max_order_amount',
        'price',
        'is_free_shipping',
        'free_shipping_threshold',
    ];

    protected function casts(): array
    {
        return [
            'min_weight' => 'decimal:4',
            'max_weight' => 'decimal:4',
            'min_order_amount' => 'decimal:4',
            'max_order_amount' => 'decimal:4',
            'price' => 'decimal:4',
            'is_free_shipping' => 'boolean',
            'free_shipping_threshold' => 'decimal:4',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(ShippingZone::class, 'zone_id');
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class, 'method_id');
    }

    public function scopeForZone(Builder $query, string $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeForMethod(Builder $query, string $methodId)
    {
        return $query->where('method_id', $methodId);
    }

    public function scopeForWeight(Builder $query, float $weight)
    {
        return $query->where(function (Builder $q) use ($weight) {
            $q->whereNull('min_weight')->orWhere('min_weight', '<=', $weight);
        })->where(function (Builder $q) use ($weight) {
            $q->whereNull('max_weight')->orWhere('max_weight', '>=', $weight);
        });
    }

    public function scopeForOrderAmount(Builder $query, float $amount)
    {
        return $query->where(function (Builder $q) use ($amount) {
            $q->whereNull('min_order_amount')->orWhere('min_order_amount', '<=', $amount);
        })->where(function (Builder $q) use ($amount) {
            $q->whereNull('max_order_amount')->orWhere('max_order_amount', '>=', $amount);
        });
    }

    public function isFreeForOrder(float $orderTotal): bool
    {
        return $this->is_free_shipping && $this->free_shipping_threshold !== null
            && $orderTotal >= $this->free_shipping_threshold;
    }

    public function calculatePrice(float $weight, float $orderTotal): float
    {
        if ($this->isFreeForOrder($orderTotal)) {
            return 0;
        }

        return (float) $this->price;
    }
}
