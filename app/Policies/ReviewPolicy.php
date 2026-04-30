<?php

namespace App\Policies;

use App\Enums\PermissionName;
use App\Models\Review;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReviewPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        // Any authenticated user can create a review (usually customers)
        return true;
    }

    public function update(User $user, Review $review): bool
    {
        // Users can update their own reviews, or staff with permission (for moderation)
        return $user->id === $review->user_id || $user->hasPermissionTo(PermissionName::UPDATE_REVIEWS->value);
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->hasPermissionTo(PermissionName::DELETE_REVIEWS->value);
    }
}
