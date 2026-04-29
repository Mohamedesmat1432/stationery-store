<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Redis;

class Wishlist extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'is_default',
        'share_token',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($wishlist) {
            if (empty($wishlist->share_token)) {
                $wishlist->share_token = bin2hex(random_bytes(16));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function scopeDefault(Builder $query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForUser(Builder $query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByToken(Builder $query, string $token)
    {
        return $query->where('share_token', $token);
    }

    public function addItem(Product $product, ?ProductVariant $variant = null): WishlistItem
    {
        return $this->items()->updateOrCreate(
            [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
            ],
            ['notes' => null, 'priority' => 0]
        );
    }

    public function removeItem(string $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
    }

    public function moveToCart(Cart $cart): void
    {
        foreach ($this->items as $item) {
            $cart->addItem($item->product, 1, $item->variant);
        }
    }

    public function getShareUrl(): string
    {
        return url("/wishlists/shared/{$this->share_token}");
    }

    public function cacheItemCount(): void
    {
        Redis::connection()->setex(
            "wishlist:{$this->id}:count",
            600,
            $this->items()->count()
        );
    }
}
