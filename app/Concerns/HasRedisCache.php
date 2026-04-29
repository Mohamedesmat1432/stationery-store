<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Redis;

trait HasRedisCache
{
    use HasCacheKey;

    public function rememberInRedis(string $key, \Closure $callback, int $ttl = 3600): mixed
    {
        $redis = Redis::connection();
        $cached = $redis->get($key);

        if ($cached !== null) {
            return json_decode($cached, true);
        }

        $value = $callback();
        $redis->setex($key, $ttl, json_encode($value));

        return $value;
    }

    public function forgetRedisCache(?string $pattern = null): void
    {
        $redis = Redis::connection();
        $pattern = $pattern ?? $this->cacheKey('*');
        $keys = $redis->keys($pattern);

        if (!empty($keys)) {
            $redis->del(...$keys);
        }
    }

    public static function flushRedisTag(): void
    {
        Redis::connection()->del(...Redis::connection()->keys(static::cacheTag() . ':*'));
    }
}
