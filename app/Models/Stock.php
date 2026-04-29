<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Redis;

class Stock extends BaseModel
{
    use HasFactory;

    protected $table = 'stocks';

    protected $fillable = [
        'product_id',
        'variant_id',
        'warehouse_id',
        'unit_id',
        'available_quantity',
        'reserved_quantity',
        'reorder_level',
        'reorder_quantity',
        'avg_cost',
        'lot_number',
        'expiry_date',
    ];

    protected function casts(): array
    {
        return [
            'available_quantity' => 'decimal:4',
            'reserved_quantity' => 'decimal:4',
            'reorder_level' => 'decimal:4',
            'reorder_quantity' => 'decimal:4',
            'avg_cost' => 'decimal:4',
            'expiry_date' => 'date',
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

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeLowStock(Builder $query)
    {
        return $query->whereColumn('available_quantity', '<=', 'reorder_level')
            ->where('reorder_level', '>', 0);
    }

    public function scopeInWarehouse(Builder $query, string $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeForProduct(Builder $query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForVariant(Builder $query, string $variantId)
    {
        return $query->where('variant_id', $variantId);
    }

    public function netQuantity(): float
    {
        return $this->available_quantity - $this->reserved_quantity;
    }

    public function isLowStock(): bool
    {
        return $this->reorder_level > 0 && $this->available_quantity <= $this->reorder_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }

    public function reserve(float $quantity): void
    {
        $this->increment('reserved_quantity', $quantity);
        $this->updateRedisStock();
    }

    public function release(float $quantity): void
    {
        $this->decrement('reserved_quantity', $quantity);
        $this->updateRedisStock();
    }

    public function deduct(float $quantity): void
    {
        $this->decrement('available_quantity', $quantity);
        $this->decrement('reserved_quantity', $quantity);
        $this->updateRedisStock();
    }

    public function add(float $quantity): void
    {
        $this->increment('available_quantity', $quantity);
        $this->updateRedisStock();
    }

    public function updateRedisStock(): void
    {
        $key = "stock:{$this->product_id}:{$this->variant_id}:{$this->warehouse_id}";
        Redis::connection()->hset($key, 'available', $this->available_quantity);
        Redis::connection()->hset($key, 'reserved', $this->reserved_quantity);
        Redis::connection()->expire($key, 3600);
    }

    public static function getFromRedis(string $productId, ?string $variantId = null, ?string $warehouseId = null): ?array
    {
        $key = "stock:{$productId}:{$variantId}:{$warehouseId}";
        $data = Redis::connection()->hgetall($key);
        return empty($data) ? null : $data;
    }
}
