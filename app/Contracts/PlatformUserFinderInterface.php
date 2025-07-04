<?php

namespace App\Contracts;

use App\Models\User;

interface PlatformUserFinderInterface
{
    public function findPlatformAdmin(): ?User;
}
