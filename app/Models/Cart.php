<?php

namespace App\Models;

use App\Actions\CheckoutAction;
use App\Enums\CartStatus;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'currency_id',
        'status',
        'subtotal',
        'tax_total',
        'discount_total',
        'shipping_total',
        'grand_total',
        'discount_id',
        'shipping_address_id',
        'billing_address_id',
        'expires_at',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'discount_total' => 'decimal:4',
            'shipping_total' => 'decimal:4',
            'grand_total' => 'decimal:4',
            'status' => CartStatus::class,
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', CartStatus::ACTIVE->value);
    }

    public function scopeForUser(Builder $query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForSession(Builder $query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function addItem(Product $product, float $quantity, ?ProductVariant $variant = null, ?Unit $unit = null): CartItem
    {
        return app(CartService::class)->addItem($this, $product, $quantity, $variant, $unit);
    }

    public function removeItem(string $itemId): void
    {
        app(CartService::class)->removeItem($this, $itemId);
    }

    public function recalculateTotals(): void
    {
        app(CartService::class)->recalculateTotals($this);
    }

    public function convertToOrder(): Order
    {
        return app(CheckoutAction::class)->execute($this);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
