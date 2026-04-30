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
        $pattern = $pattern ?? $this->cacheKey('*');
        static::deleteRedisByPattern($pattern);
    }

    public static function flushRedisTag(): void
    {
        $pattern = static::cacheTag().':*';
        static::deleteRedisByPattern($pattern);
    }

    /**
     * Delete all Redis keys matching the given pattern using non-blocking SCAN.
     */
    protected static function deleteRedisByPattern(string $pattern): void
    {
        $redis = Redis::connection();
        $cursor = '0';

        do {
            [$cursor, $keys] = $redis->scan($cursor, ['MATCH' => $pattern, 'COUNT' => 100]);
            if (! empty($keys)) {
                $redis->del(...$keys);
            }
        } while ($cursor !== '0');
    }
}
