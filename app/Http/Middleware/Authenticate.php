<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

/**
 * @method bool hasRole(string|array|\Spatie\Permission\Contracts\Role $roles)
 */
class Authenticate extends Middleware
{
    /**
     * 定義未登入時的重定向路徑
     */
    protected function redirectTo(Request $request): ?string
    {
        // 如果請求期望 JSON，則不進行重定向
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * 處理進來的請求
     */
    public function handle($request, Closure $next, ...$guards)
    {
        // 檢查用戶是否已登入
        if (!Auth::check()) {
            // 如果是 AJAX 請求，返回 JSON 錯誤
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['error' => '!login'], 401);
            }

            // 非 AJAX 請求，清除 session 並重定向到登入頁面
            Session::flush(); // 清除所有舊的 session
            return redirect()->route('login')->with('error', '登入時效已過，請重新登入');
        }

        $user = Auth::user();

        // 🔍 檢查是否為 arcade-staff 或 machine-staff，並檢查 parent 是否啟用
        if ($user->hasRole(['arcade-staff', 'machine-staff']) && $user->parent && !$user->is_active) {
            // 如果是 AJAX 請求，返回 JSON 錯誤
            if ($request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                Auth::logout(); // 強制登出
                Session::flush(); // 清除 session
                return response()->json(['error' => 'account_disabled', 'message' => '您的管理員帳戶已停用，洽詢您的上級管理員。'], 403);
            }

            // 非 AJAX 請求，執行標準登出和重定向
            Auth::logout(); // 強制登出
            Session::flush(); // 清除 session
            return redirect()->route('login')->withErrors(['account' => '您的管理員帳戶已停用，洽詢您的上級管理員。']);
        }

        return $next($request);
    }
}
