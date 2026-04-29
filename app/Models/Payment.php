<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Payment extends BaseModel
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'order_id',
        'method_id',
        'amount',
        'currency_code',
        'exchange_rate',
        'status',
        'transaction_id',
        'gateway_reference',
        'gateway_response',
        'failure_reason',
        'paid_at',
        'refunded_at',
        'refunded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'exchange_rate' => 'decimal:6',
            'status' => PaymentStatus::class,
            'paid_at' => 'datetime',
            'refunded_at' => 'datetime',
            'gateway_response' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount', 'transaction_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function method(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    public function refunder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', PaymentStatus::PENDING->value);
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', PaymentStatus::PAID->value);
    }

    public function scopeFailed(Builder $query)
    {
        return $query->where('status', PaymentStatus::FAILED->value);
    }

    public function scopeForOrder(Builder $query, string $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => PaymentStatus::PAID->value,
            'paid_at' => now(),
        ]);

        $this->order->markAsPaid();
    }

    public function markAsFailed(?string $reason = null): void
    {
        $this->update([
            'status' => PaymentStatus::FAILED->value,
            'failure_reason' => $reason,
        ]);
    }

    public function markAsRefunded(?string $refundedBy = null): void
    {
        $this->update([
            'status' => PaymentStatus::REFUNDED->value,
            'refunded_at' => now(),
            'refunded_by' => $refundedBy,
        ]);
    }

    public function isSuccessful(): bool
    {
        return $this->status === PaymentStatus::PAID;
    }
}
