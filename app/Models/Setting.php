<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class Setting extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'value' => 'array',
        ];
    }

    public function scopeForGroup(Builder $query, string $group)
    {
        return $query->where('group', $group);
    }

    public function scopePublic(Builder $query)
    {
        return $query->where('is_public', true);
    }

    public function getTypedValue()
    {
        return match ($this->type) {
            'boolean' => (bool) $this->value,
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'json', 'array' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $cacheKey = "setting:{$key}";

        return Cache::store('redis')->remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting?->getTypedValue() ?? $default;
        });
    }

    public static function set(string $key, mixed $value, string $type = 'string', ?string $group = null): self
    {
        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) || is_object($value) ? json_encode($value) : $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::store('redis')->forget("setting:{$key}");
        return $setting;
    }

    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::store('redis')->forget("setting:{$key}");
    }

    public static function getGroup(string $group): array
    {
        return static::forGroup($group)
            ->get()
            ->mapWithKeys(fn($s) => [$s->key => $s->getTypedValue()])
            ->toArray();
    }

    public static function flushCache(): void
    {
        $redis = Redis::connection();
        $keys = $redis->keys('setting:*');
        if (!empty($keys)) {
            $redis->del(...$keys);
        }
    }
}
