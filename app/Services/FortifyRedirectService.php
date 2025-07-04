<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

class FortifyRedirectService
{
    public function __invoke()
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        } elseif ($user->hasRole('arcade-owner')) {
            return route('arcades.dashboard');
        } elseif ($user->hasRole('arcade-staff')) {
            return route('arcades.dashboard');
        } elseif ($user->hasRole('machine-owner')) {
            return route('machines.dashboard');
        } elseif ($user->hasRole('machine-manager')) {
            return route('machines.dashboard');
        } elseif ($user->hasRole('member')) {
            return route('member.dashboard');
        } elseif ($user->hasRole('user')) {
            return route('user.dashboard');
        }

        return '/dashboard'; // 預設路徑
    }
}
