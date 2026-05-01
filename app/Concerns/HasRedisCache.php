<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Redis;

trait HasRedisCache
{
    use HasCacheKey;

    public function rememberInRedis(string $key, \Closure $callback, int $ttl = 3600): mixed
    {
        try {
            $redis = Redis::connection();
            $cached = $redis->get($key);

            if ($cached !== null) {
                return json_decode($cached, true);
            }

            $value = $callback();
            $redis->setex($key, $ttl, json_encode($value));

            return $value;
        } catch (\Throwable $e) {
            // Log error if needed, but fallback to direct callback to keep the app running
            return $callback();
        }
    }

    public function forgetRedisCache(?string $pattern = null): void
    {
        $pattern = $pattern ?? $this->cacheKey('*');
        static::deleteRedisByPattern($pattern);
    }

    public static function flushRedisTag(): void
    {
        $pattern = static::cacheTag() . ':*';
        static::deleteRedisByPattern($pattern);
    }

    /**
     * Delete all Redis keys matching the given pattern using non-blocking SCAN.
     */
    protected static function deleteRedisByPattern(string $pattern): void
    {
        $redisConnection = Redis::connection();
        $client = $redisConnection->client();
        $prefix = (string) config('database.redis.options.prefix');

        $matchPattern = $prefix . $pattern;
        $cursor = null;

        while (($keys = $client->scan($cursor, $matchPattern, 100)) !== false) {
            if (!empty($keys)) {
                // phpredis scan returns prefixed keys.
                // We must strip the prefix before passing them back to DEL via the connection,
                // as the connection will re-prefix them.
                if ($prefix) {
                    $keys = array_map(function ($key) use ($prefix) {
                        return str_starts_with($key, $prefix) ? substr($key, strlen($prefix)) : $key;
                    }, $keys);
                }

                $redisConnection->del(...$keys);
            }

            if ($cursor == 0) {
                break;
            }
        }
    }
}
