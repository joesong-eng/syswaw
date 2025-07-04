<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();
        // 驗證完成後根據角色跳轉
        $user = $request->user();
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME . '?verified=1');
        }
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }
        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('arcade-owner')) {
            return redirect()->route('arcades.dashboard');
        } elseif ($user->hasRole('arcade-staff')) {
            return redirect()->route('arcades.dashboard');
        } elseif ($user->hasRole('machine-owner')) {
            return redirect()->route('machines.dashboard');
        } elseif ($user->hasRole('machine-staff')) {
            return redirect()->route('machines.dashboard');
        } elseif ($user->hasRole('member')) {
            return redirect()->route('member.dashboard');
        } elseif ($user->hasRole('user')) {
            return redirect()->route('user.dashboard');
        }

        return redirect('/dashboard'); // 預設跳轉
    }
}
