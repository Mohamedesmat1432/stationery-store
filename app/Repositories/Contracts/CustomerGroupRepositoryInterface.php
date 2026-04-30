<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface CustomerGroupRepositoryInterface extends RepositoryInterface
{
    public function allActive(): Collection;
}
