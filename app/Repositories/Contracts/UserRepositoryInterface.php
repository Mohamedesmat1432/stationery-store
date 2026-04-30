<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function syncRoles(User $user, array $roles): void;
}
