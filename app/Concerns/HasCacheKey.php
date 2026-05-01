<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 *
 * @method mixed getKey()
 */
trait HasCacheKey
{
    public function cacheKey(string $suffix = ''): string
    {
        return sprintf(
            '%s:%s%s',
            class_basename($this),
            $this->getKey(),
            $suffix ? ':'.$suffix : ''
        );
    }

    public static function cacheTag(): string
    {
        return class_basename(static::class);
    }
}
