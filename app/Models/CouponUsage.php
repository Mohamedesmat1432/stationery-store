<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends BaseModel
{
    use HasFactory;

    protected $table = 'coupon_usages';

    protected $fillable = [
        'discount_id',
        'user_id',
        'order_id',
        'cart_id',
        'amount_saved',
    ];

    protected function casts(): array
    {
        return [
            'amount_saved' => 'decimal:2',
        ];
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeForDiscount(Builder $query, string $discountId)
    {
        return $query->where('discount_id', $discountId);
    }

    public function scopeForUser(Builder $query, string $userId)
    {
        return $query->where('user_id', $userId);
    }
}
