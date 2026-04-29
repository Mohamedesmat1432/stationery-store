<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductView extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'referrer',
        'source',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForProduct(Builder $query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForDateRange(Builder $query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public static function track(string $productId, ?string $variantId = null, ?string $userId = null): void
    {
        static::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'user_id' => $userId,
            'session_id' => session()->getId(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'referrer' => request()->headers->get('referer'),
            'source' => request()->get('source', 'direct'),
        ]);
    }
}
