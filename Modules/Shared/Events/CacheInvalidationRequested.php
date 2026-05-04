<?php

namespace Modules\Shared\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event dispatched when a model change requires cache invalidation.
 *
 * This centralizes cache invalidation logic in listeners rather than
 * scattering it across model observers, improving testability and
 * separation of concerns.
 */
class CacheInvalidationRequested
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  string  $tag  The cache tag to invalidate (e.g., 'users', 'customers').
     * @param  string|null  $specificKey  Optional specific cache key to invalidate.
     * @param  array<string>  $additionalTags  Additional tags to invalidate.
     */
    public function __construct(
        public string $tag,
        public ?string $specificKey = null,
        public array $additionalTags = [],
    ) {}
}
