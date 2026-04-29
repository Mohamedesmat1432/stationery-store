<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\FulfillmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Facades\Redis;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends BaseModel
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_number',
        'user_id',
        'guest_email',
        'customer_id',
        'currency_id',
        'exchange_rate',
        'subtotal',
        'tax_total',
        'discount_total',
        'shipping_total',
        'grand_total',
        'status',
        'payment_status',
        'fulfillment_status',
        'discount_id',
        'coupon_code',
        'shipping_method_id',
        'shipping_address_id',
        'billing_address_id',
        'customer_notes',
        'admin_notes',
        'ip_address',
        'user_agent',
        'source',
        'tracking_number',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'refunded_at',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'subtotal' => 'decimal:4',
            'tax_total' => 'decimal:4',
            'discount_total' => 'decimal:4',
            'shipping_total' => 'decimal:4',
            'grand_total' => 'decimal:4',
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'fulfillment_status' => FulfillmentStatus::class,
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });

        static::updated(function ($order) {
            if ($order->wasChanged('status')) {
                $order->statusHistories()->create([
                    'from_status' => $order->getOriginal('status'),
                    'to_status' => $order->status,
                    'changed_by_type' => 'system',
                ]);
            }
            $order->forgetRedisCache();
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'fulfillment_status', 'tracking_number'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function successfulPayments(): HasMany
    {
        return $this->payments()->where('status', PaymentStatus::PAID->value);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function shippingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'shipping_address_id');
    }

    public function billingAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'billing_address_id');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', OrderStatus::PENDING->value);
    }

    public function scopeProcessing(Builder $query)
    {
        return $query->where('status', OrderStatus::PROCESSING->value);
    }

    public function scopeShipped(Builder $query)
    {
        return $query->where('status', OrderStatus::SHIPPED->value);
    }

    public function scopeDelivered(Builder $query)
    {
        return $query->where('status', OrderStatus::DELIVERED->value);
    }

    public function scopeCancelled(Builder $query)
    {
        return $query->where('status', OrderStatus::CANCELLED->value);
    }

    public function scopePaid(Builder $query)
    {
        return $query->where('payment_status', PaymentStatus::PAID->value);
    }

    public function scopeUnpaid(Builder $query)
    {
        return $query->where('payment_status', PaymentStatus::PENDING->value);
    }

    public function scopeForDateRange(Builder $query, string $from, string $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeForCustomer(Builder $query, string $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public static function generateOrderNumber(): string
    {
        $prefix = 'ORD-' . now()->format('Y');
        $lastOrder = static::withTrashed()
            ->where('order_number', 'like', $prefix . '-%')
            ->orderBy('order_number', 'desc')
            ->value('order_number');

        $sequence = $lastOrder
            ? (int) substr($lastOrder, -7) + 1
            : 1;

        return $prefix . '-' . str_pad((string) $sequence, 7, '0', STR_PAD_LEFT);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);
    }

    public function markAsShipped(?string $trackingNumber = null): void
    {
        $this->update([
            'status' => OrderStatus::SHIPPED->value,
            'fulfillment_status' => FulfillmentStatus::FULFILLED->value,
            'shipped_at' => now(),
            'tracking_number' => $trackingNumber,
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => OrderStatus::DELIVERED->value,
            'delivered_at' => now(),
        ]);
    }

    public function markAsCancelled(?string $reason = null): void
    {
        $this->update([
            'status' => OrderStatus::CANCELLED->value,
            'cancelled_at' => now(),
        ]);

        if ($reason) {
            $this->statusHistories()->create([
                'from_status' => $this->getOriginal('status'),
                'to_status' => OrderStatus::CANCELLED->value,
                'notes' => $reason,
                'changed_by_type' => 'system',
            ]);
        }
    }

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::PAID;
    }

    public function isShipped(): bool
    {
        return $this->status === OrderStatus::SHIPPED;
    }

    public function isDelivered(): bool
    {
        return $this->status === OrderStatus::DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status->value, [
            OrderStatus::PENDING->value,
            OrderStatus::PROCESSING->value,
        ]);
    }

    public function getTotalRefunded(): float
    {
        return (float) $this->refunds()
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getRemainingBalance(): float
    {
        return $this->grand_total - $this->getTotalRefunded();
    }

    public function cacheOrderSummary(): void
    {
        $key = "order:{$this->id}:summary";
        Redis::connection()->setex($key, 3600, json_encode([
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status->value,
            'grand_total' => $this->grand_total,
            'item_count' => $this->items()->count(),
        ]));
    }
}
