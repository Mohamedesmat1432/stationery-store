<?php

namespace Modules\Shared\Services\Cache;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

abstract class BaseCacheService
{
    public const TAG_GLOBAL = 'global';

    private static array $versionCache = [];

    /**
     * Clear the local version cache to prevent stale data in Octane/Swoole.
     */
    public static function clearVersionCache(): void
    {
        self::$versionCache = [];
    }

    protected static function getVersionedKey(string $tag, string $key): string
    {
        $globalVersion = self::getTagVersion(self::TAG_GLOBAL);
        $tagVersion = self::getTagVersion($tag);

        return "v7:g{$globalVersion}:t{$tagVersion}:{$tag}:{$key}";
    }

    public static function getTagVersion(string $tag): string
    {
        if (isset(self::$versionCache[$tag])) {
            return self::$versionCache[$tag];
        }

        $versionKey = "version:{$tag}";
        $version = Cache::get($versionKey);

        if (! $version) {
            $version = (string) (int) (microtime(true) * 10000);
            // Use add() for atomic initialization to prevent race conditions
            if (! Cache::add($versionKey, $version)) {
                $version = Cache::get($versionKey);
            }
        }

        return self::$versionCache[$tag] = (string) $version;
    }

    /**
     * Remember paginated results with Pre-Cache Transformation.
     */
    public static function rememberPaginated(
        string $tag,
        array $params,
        int $perPage,
        callable $callback,
        ?callable $transform = null,
        int $ttl = 3600
    ): array {
        $stableParams = array_intersect_key($params, array_flip(['page', 'filter', 'per_page', 'sort']));
        $paramKey = md5(serialize($stableParams));
        $fullKey = self::getVersionedKey($tag, "paginated:p{$perPage}:{$paramKey}");

        return Cache::remember($fullKey, $ttl, function () use ($callback, $transform) {
            $paginator = $callback();

            if ($transform && $paginator instanceof LengthAwarePaginator) {
                $paginator->setCollection(
                    collect($transform($paginator->getCollection()))
                );
            }

            return $paginator instanceof Arrayable
                ? $paginator->toArray()
                : (array) $paginator;
        });
    }

    /**
     * Remember direct results with Pre-Cache Transformation.
     */
    public static function rememberDirect(
        string $tag,
        string $key,
        callable $callback,
        ?callable $transform = null,
        int $ttl = 3600
    ): mixed {
        $fullKey = self::getVersionedKey($tag, "direct:{$key}");

        return Cache::remember($fullKey, $ttl, function () use ($callback, $transform) {
            $result = $callback();

            return $transform ? $transform($result) : $result;
        });
    }

    protected static function flushTagsWithFallbacks(array $tags, array $keys = []): void
    {
        foreach ($tags as $tag) {
            self::incrementTagVersion($tag);
        }
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    protected static function incrementTagVersion(string $tag): void
    {
        unset(self::$versionCache[$tag]);
        $versionKey = "version:{$tag}";

        // If key doesn't exist, it will be initialized by the next getTagVersion call.
        // If it does, it's incremented atomically.
        if (! Cache::increment($versionKey)) {
            // If increment returns false/null (driver dependent if key missing),
            // we initialize it.
            self::getTagVersion($tag);
        }
    }
}
