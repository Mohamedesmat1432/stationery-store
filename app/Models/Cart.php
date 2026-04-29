<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Redis;

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
        $unit = $unit ?? $product->productUnits()->where('is_default', true)->first()?->unit;
        $variant = $variant ?? $product->defaultVariant();

        $price = $variant
            ? $variant->prices()->active()->forCurrency($this->currency_id)->first()
            : $product->prices()->active()->forCurrency($this->currency_id)->first();

        $unitPrice = $price?->amount ?? 0;
        $subtotal = $unitPrice * $quantity;

        $item = $this->items()->updateOrCreate(
            [
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'unit_id' => $unit?->id,
            ],
            [
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
            ]
        );

        $this->recalculateTotals();
        return $item;
    }

    public function removeItem(string $itemId): void
    {
        $this->items()->where('id', $itemId)->delete();
        $this->recalculateTotals();
    }

    public function recalculateTotals(): void
    {
        $subtotal = $this->items()->sum('subtotal');
        $this->update([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal + $this->tax_total + $this->shipping_total - $this->discount_total,
        ]);

        Redis::connection()->setex("cart:{$this->id}:totals", 300, json_encode([
            'subtotal' => $subtotal,
            'grand_total' => $subtotal + $this->tax_total + $this->shipping_total - $this->discount_total,
        ]));
    }

    public function convertToOrder(): Order
    {
        return \DB::transaction(function () {
            $order = Order::create([
                'user_id' => $this->user_id,
                'customer_id' => $this->user?->customer?->id,
                'currency_id' => $this->currency_id,
                'subtotal' => $this->subtotal,
                'tax_total' => $this->tax_total,
                'discount_total' => $this->discount_total,
                'shipping_total' => $this->shipping_total,
                'grand_total' => $this->grand_total,
                'status' => OrderStatus::PENDING->value,
                'shipping_address_id' => $this->shipping_address_id,
                'billing_address_id' => $this->billing_address_id,
                'discount_id' => $this->discount_id,
                'ip_address' => $this->ip_address,
                'user_agent' => $this->user_agent,
            ]);

            foreach ($this->items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'variant_id' => $item->variant_id,
                    'unit_id' => $item->unit_id,
                    'name' => $item->product->name,
                    'sku' => $item->variant?->sku ?? $item->product->sku,
                    'image' => $item->product->getFeaturedImageUrl('thumb'),
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            $this->update(['status' => CartStatus::CONVERTED->value]);

            return $order;
        });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
