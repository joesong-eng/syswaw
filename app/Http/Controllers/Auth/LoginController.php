<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\NotificationEvent;
use App\Models\User;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    // public function login(Request $request)
    // {
    //     // 驗證登入表單的輸入
    //     $credentials = $request->validate([
    //         'email' => ['required', 'email'],
    //         'password' => ['required'],
    //     ]);
    
    //     // 嘗試登入
    //     if (Auth::attempt($credentials)) {
    //         // 登入成功後，重新生成會話
    //         $request->session()->regenerate();
    //         // 檢查用戶的 is_active 狀態
    //         if (!Auth::user()->is_active) {
    //             // 如果帳號未被啟用，登出用戶並返回錯誤信息
    //             Auth::logout();
    //             return back()->withErrors([
    //                 'email' => 'Your account is not active. Please contact support.帳戶鎖定，請聯繫客服。',
    //             ])->withInput($request->except('password'));
    //         }
    //         // 根據角色重定向
    //         if (Auth::user()->hasRole('admin')) {
    //             return redirect()->intended('admin/dashboard');
    //         }
    //         $adminUsers = User::role('Admin')->get();
    //         event(new NotificationEvent('User has logged in: ' . Auth::user()->name, $adminUsers, url('/admin/users')));
    //         return redirect()->intended('dashboard');
    //     }
    
    //     // 登入失敗，返回錯誤信息
    //     return back()->withErrors([
    //         'email' => 'The provided credentials do not match our records.',
    //     ])->withInput($request->except('password'));
    // }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}