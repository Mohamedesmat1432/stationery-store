<?php

namespace App\Models;

use App\Enums\PriceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Price extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'priceable_type',
        'priceable_id',
        'amount',
        'compare_at_price',
        'cost_price',
        'currency_id',
        'type',
        'customer_group_id',
        'start_at',
        'end_at',
        'min_quantity',
        'max_quantity',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'compare_at_price' => 'decimal:4',
            'cost_price' => 'decimal:4',
            'type' => PriceType::class,
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'min_quantity' => 'integer',
            'max_quantity' => 'integer',
        ];
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('start_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }

    public function scopeForCurrency(Builder $query, string $currencyId)
    {
        return $query->where('currency_id', $currencyId);
    }

    public function scopeForType(Builder $query, PriceType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeForGroup(Builder $query, ?string $groupId)
    {
        return $query->where(function ($q) use ($groupId) {
            $q->whereNull('customer_group_id')
                ->orWhere('customer_group_id', $groupId);
        });
    }

    public function scopeForQuantity(Builder $query, int $quantity)
    {
        return $query->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')->orWhere('max_quantity', '>=', $quantity);
            });
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_price !== null && $this->compare_at_price > $this->amount;
    }

    public function discountPercentage(): float
    {
        if (! $this->isOnSale()) {
            return 0;
        }

        return round((($this->compare_at_price - $this->amount) / $this->compare_at_price) * 100, 2);
    }

    public function marginPercentage(): ?float
    {
        if (! $this->cost_price || $this->cost_price <= 0) {
            return null;
        }

        return round((($this->amount - $this->cost_price) / $this->amount) * 100, 2);
    }
}
