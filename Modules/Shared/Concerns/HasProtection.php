<?php

namespace Modules\Shared\Concerns;

use App\Models\User;

trait HasProtection
{
    /**
     * Determine if the model is protected from deletion or modification.
     */
    public function isProtected(?User $user = null): bool
    {
        if (method_exists($this, 'shouldBeProtected')) {
            return $this->shouldBeProtected($user);
        }

        return false;
    }

    /**
     * Ensure the model is not protected before performing a sensitive action.
     *
     * @throws \RuntimeException
     */
    public function ensureNotProtected(?User $user = null): void
    {
        if ($this->isProtected($user)) {
            throw new \RuntimeException(__('This resource is protected and cannot be modified or deleted.'));
        }
    }
}
