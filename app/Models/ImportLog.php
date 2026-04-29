<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'file_path',
        'status',
        'total_rows',
        'processed_rows',
        'failed_rows',
        'errors',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'total_rows' => 'integer',
            'processed_rows' => 'integer',
            'failed_rows' => 'integer',
            'errors' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed(Builder $query)
    {
        return $query->where('status', 'failed');
    }

    public function progressPercentage(): float
    {
        if ($this->total_rows === 0) return 0;
        return round(($this->processed_rows / $this->total_rows) * 100, 2);
    }
}
