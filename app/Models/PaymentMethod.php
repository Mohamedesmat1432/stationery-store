<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'provider',
        'config',
        'is_active',
        'is_test_mode',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
            'is_test_mode' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'method_id');
    }

    public function scopeOnline(Builder $query)
    {
        return $query->where('type', 'online');
    }

    public function scopeOffline(Builder $query)
    {
        return $query->where('type', 'offline');
    }

    public function getConfigValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }
}
