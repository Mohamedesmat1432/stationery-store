<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment_id',
        'order_item_id',
        'amount',
        'reason',
        'notes',
        'status',
        'gateway_refund_id',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'processed_at' => 'datetime',
            'status' => RefundStatus::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', RefundStatus::PENDING->value);
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', RefundStatus::COMPLETED->value);
    }

    public function scopeForOrder(Builder $query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => RefundStatus::COMPLETED->value,
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => RefundStatus::FAILED->value,
            'notes' => $reason,
        ]);
    }
}
