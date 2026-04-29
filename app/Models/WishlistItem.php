<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'product_id',
        'variant_id',
        'notes',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'priority' => 'integer',
        ];
    }

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function scopeForWishlist(Builder $query, string $wishlistId)
    {
        return $query->where('wishlist_id', $wishlistId);
    }
}
