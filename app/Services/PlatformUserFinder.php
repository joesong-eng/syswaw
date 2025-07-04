<?php

namespace App\Services;

use App\Contracts\PlatformUserFinderInterface;
use App\Models\User;

class PlatformUserFinder implements PlatformUserFinderInterface
{
    public function findPlatformAdmin(): ?User
    {
        return User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })->orderBy('id')->first();
    }
}
