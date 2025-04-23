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
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    public function handle($request, Closure $next, ...$guards)
    {
        if (!Auth::check()) {
            Session::flush(); // 清除所有舊的 session
            return redirect()->route('login')->with('error', '登入時效已過，請重新登入');
        }

        $user = Auth::user();
        // echo $user->parent->id."Authenticate Middleware";

        // 🔍 檢查是否為 arcade-staff 或 machine-staff，並檢查 parent 是否啟用
        if ($user->hasRole(['arcade-staff', 'machine-staff']) && $user->parent && !$user->is_active) {
            Auth::logout(); // 強制登出
            Session::flush(); // 清除 session
            return redirect()->route('login')->withErrors(['account' => '您的管理員帳戶已停用，洽詢您的上級管理員。']);
        }

        return $next($request);
    }
}
