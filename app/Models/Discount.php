<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\DiscountType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Redis;

class Discount extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'value',
        'applies_to',
        'min_purchase_amount',
        'usage_limit',
        'usage_count',
        'per_customer_limit',
        'free_shipping',
        'is_stackable',
        'is_auto_apply',
        'is_active',
        'start_at',
        'end_at',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
            'min_purchase_amount' => 'decimal:4',
            'usage_count' => 'integer',
            'per_customer_limit' => 'integer',
            'free_shipping' => 'boolean',
            'is_stackable' => 'boolean',
            'is_auto_apply' => 'boolean',
            'is_active' => 'boolean',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'type' => DiscountType::class,
        ];
    }

    public function discountables(): HasMany
    {
        return $this->hasMany(Discountable::class);
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'discountable');
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'discountable');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_at')->orWhere('start_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>=', now());
            });
    }

    public function scopeCoupon(Builder $query)
    {
        return $query->whereNotNull('code');
    }

    public function scopeAutoApply(Builder $query)
    {
        return $query->where('is_auto_apply', true);
    }

    public function scopeByCode(Builder $query, string $code)
    {
        return $query->where('code', $code);
    }

    public function scopeForProduct(Builder $query, string $productId)
    {
        return $query->where(function ($q) use ($productId) {
            $q->where('applies_to', 'all')
                ->orWhereHas(
                    'discountables',
                    fn($dq) => $dq
                        ->where('discountable_type', Product::class)
                        ->where('discountable_id', $productId)
                );
        });
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->start_at && $this->start_at->isFuture()) return false;
        if ($this->end_at && $this->end_at->isPast()) return false;
        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) return false;
        return true;
    }

    public function canBeUsedBy(string $userId): bool
    {
        if (!$this->isValid()) return false;
        if ($this->per_customer_limit === null) return true;

        $usedCount = Order::where('discount_id', $this->id)
            ->where('user_id', $userId)
            ->count();

        return $usedCount < $this->per_customer_limit;
    }

    public function calculateDiscount(float $subtotal): float
    {
        return match ($this->type) {
            DiscountType::PERCENTAGE => round($subtotal * ($this->value / 100), 4),
            DiscountType::FIXED_AMOUNT => min($this->value, $subtotal),
            DiscountType::FREE_SHIPPING => 0,
            default => 0,
        };
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->forgetRedisCache();
    }

    public function getCacheKey(string $suffix = ''): string
    {
        return "discount:{$this->id}" . ($suffix ? ":{$suffix}" : '');
    }

    public static function getActiveCouponsFromCache(): array
    {
        $redis = Redis::connection();
        $keys = $redis->keys('discount:*:active');
        $coupons = [];

        foreach ($keys as $key) {
            $data = $redis->get($key);
            if ($data) $coupons[] = json_decode($data, true);
        }

        return $coupons;
    }

    public function cacheActiveStatus(): void
    {
        Redis::connection()->setex(
            $this->getCacheKey('active'),
            3600,
            json_encode([
                'id' => $this->id,
                'code' => $this->code,
                'type' => $this->type->value,
                'value' => $this->value,
                'is_valid' => $this->isValid(),
            ])
        );
    }
}
