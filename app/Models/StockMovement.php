<?php

namespace App\Models;

use App\Enums\StockMovementType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'unit_id',
        'quantity',
        'before_quantity',
        'after_quantity',
        'unit_cost',
        'type',
        'lot_number',
        'notes',
        'reference_type',
        'reference_id',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'before_quantity' => 'decimal:4',
            'after_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'type' => StockMovementType::class,
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForProduct(Builder $query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForWarehouse(Builder $query, string $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeOfType(Builder $query, StockMovementType $type)
    {
        return $query->where('type', $type->value);
    }

    public function scopeInDateRange(Builder $query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
