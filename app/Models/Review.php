<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Review extends BaseModel
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'product_id',
        'order_id',
        'user_id',
        'author_name',
        'author_email',
        'rating',
        'title',
        'comment',
        'is_approved',
        'is_verified_purchase',
        'helpful_count',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_verified_purchase' => 'boolean',
            'helpful_count' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rating', 'is_approved'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending(Builder $query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeForProduct(Builder $query, string $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeVerified(Builder $query)
    {
        return $query->where('is_verified_purchase', true);
    }

    public function scopeWithRating(Builder $query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    public function approve(): void
    {
        $this->update(['is_approved' => true]);
        $this->product->recalculateRating();
    }

    public function reject(): void
    {
        $this->update(['is_approved' => false]);
        $this->product->recalculateRating();
    }

    public function markHelpful(): void
    {
        $this->increment('helpful_count');
    }

    public function isVerifiedPurchase(): bool
    {
        return $this->is_verified_purchase;
    }
}
