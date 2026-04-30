<?php

namespace App\Models;

use App\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'shipping_method_id',
        'shipping_rate_id',
        'status',
        'tracking_number',
        'carrier',
        'label_url',
        'weight',
        'dimensions',
        'shipping_cost',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'dimensions' => 'array',
            'shipping_cost' => 'decimal:4',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
            'status' => ShipmentStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function shippingRate(): BelongsTo
    {
        return $this->belongsTo(ShippingRate::class);
    }

    public function scopeForOrder(Builder $query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', ShipmentStatus::PENDING->value);
    }

    public function scopeShipped(Builder $query)
    {
        return $query->where('status', ShipmentStatus::SHIPPED->value);
    }

    public function scopeDelivered(Builder $query)
    {
        return $query->where('status', ShipmentStatus::DELIVERED->value);
    }

    public function markAsShipped(): void
    {
        $this->update([
            'status' => ShipmentStatus::SHIPPED->value,
            'shipped_at' => now(),
        ]);

        $this->order->markAsShipped($this->tracking_number);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => ShipmentStatus::DELIVERED->value,
            'delivered_at' => now(),
        ]);

        $this->order->markAsDelivered();
    }

    public function getTrackingUrl(): ?string
    {
        if (! $this->tracking_number || ! $this->carrier) {
            return null;
        }

        return match ($this->carrier) {
            'fedex' => "https://www.fedex.com/apps/fedextrack/?tracknumbers={$this->tracking_number}",
            'ups' => "https://www.ups.com/track?tracknum={$this->tracking_number}",
            'dhl' => "https://www.dhl.com/en/express/tracking.html?AWB={$this->tracking_number}&brand=DHL",
            'aramex' => "https://www.aramex.com/track/?ShipmentNumber={$this->tracking_number}",
            default => null,
        };
    }
}
