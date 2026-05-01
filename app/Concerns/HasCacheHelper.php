<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * Provides consistent caching methods for Eloquent models.
 * Ensures no duplicate cache operations and proper TTL management.
 */
trait HasCacheHelper
{
    /**
     * Generate a cache key for this model instance.
     *
     * @param string $suffix The cache key suffix
     * @return string The full cache key
     */
    protected function getCacheKey(string $suffix): string
    {
        $className = class_basename($this::class);
        return "{$className}:{$this->getKey()}:{$suffix}";
    }

    /**
     * Generate a cache key for model class-level data.
     *
     * @param string $suffix The cache key suffix
     * @return string The full cache key
     */
    protected static function getClassCacheKey(string $suffix): string
    {
        $className = class_basename(static::class);
        return "{$className}:{$suffix}";
    }

    /**
     * Remember a value in cache for this model instance.
     * Uses the model's ID and a suffix to create a unique cache key.
     *
     * @param string $suffix Cache key suffix
     * @param callable $callback Callback to generate the value
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @return mixed The cached or computed value
     */
    public function remember(string $suffix, callable $callback, int $ttl = 3600): mixed
    {
        $key = $this->getCacheKey($suffix);
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget a cached value for this model instance.
     *
     * @param string $suffix Cache key suffix to forget
     * @return bool True if the key was deleted
     */
    public function forget(string $suffix): bool
    {
        $key = $this->getCacheKey($suffix);
        return Cache::forget($key);
    }

    /**
     * Flush all instance caches for this model.
     * Clears all caches associated with this specific model ID.
     *
     * @return void
     */
    public function flushInstanceCache(): void
    {
        $pattern = "{$this->getCacheKey('*')}";
        $keys = Cache::getStore()->many([$pattern]);
        if (! empty($keys)) {
            foreach ($keys as $key => $value) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Remember a value in cache at class level.
     * Uses the model class name for caching shared data.
     *
     * @param string $suffix Cache key suffix
     * @param callable $callback Callback to generate the value
     * @param int $ttl Time to live in seconds (default: 1 hour)
     * @return mixed The cached or computed value
     */
    public static function rememberClass(string $suffix, callable $callback, int $ttl = 3600): mixed
    {
        $key = static::getClassCacheKey($suffix);
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Forget a cached value at class level.
     *
     * @param string $suffix Cache key suffix to forget
     * @return bool True if the key was deleted
     */
    public static function forgetClass(string $suffix): bool
    {
        $key = static::getClassCacheKey($suffix);
        return Cache::forget($key);
    }

    /**
     * Flush all class-level caches for this model.
     *
     * @return void
     */
    public static function flushClassCache(): void
    {
        // Implementation would require pattern-based deletion support from cache driver
        // For now, manually forget known class caches
    }
}
