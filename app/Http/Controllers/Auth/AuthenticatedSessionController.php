<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        // 檢查用戶是否被激活
        if (!$user->is_active) {
            Auth::logout(); // 確保登出未激活的用戶
            return redirect()->route('login')->withErrors(['account' => __('auth.account_deactivated_contact_admin')]);
        }

        // 確保用戶有角色，並獲取第一個角色名稱
        $rolename = $user->getRoleNames()->first();

        // 若沒有角色，跳轉到默認首頁
        if (empty($rolename)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // 檢查角色名稱是否在允許的白名單中
        $allowedRoles = [
            'admin' => 'admin',
            'arcade-owner' => 'arcade',
            'arcade-staff' => 'arcade',
            'machine-owner' => 'machine',
            'machine-manager' => 'machine',
            'member' => '',
            'user' => ''
        ];

        if (!array_key_exists($rolename, $allowedRoles)) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $route = empty($rolename) ? 'dashboard' : $allowedRoles[$rolename] . '.dashboard';

        // 安全地重定向到對應角色的 Dashboard
        return redirect()->route($route);
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
