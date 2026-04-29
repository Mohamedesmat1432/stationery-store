<?php

namespace App\Concerns;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method mixed getKey()
 */

trait HasCacheKey
{
    public function cacheKey(string $suffix = ''): string
    {
        return sprintf(
            '%s:%s:%s%s',
            class_basename($this),
            $this->getKey(),
            $this->updated_at?->timestamp ?? 'new',
            $suffix ? ':' . $suffix : ''
        );
    }

    public static function cacheTag(): string
    {
        return strtolower(class_basename(static::class)) . 's';
    }
}
