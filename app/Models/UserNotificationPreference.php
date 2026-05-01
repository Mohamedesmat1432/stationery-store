<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotificationPreference extends BaseModel
{
    use HasFactory;

    protected $table = 'user_notification_preferences';

    protected $fillable = [
        'user_id',
        'channel',
        'type',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForChannel(Builder $query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeEnabled(Builder $query)
    {
        return $query->where('is_enabled', true);
    }
}
