<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // --- 新增：根據角色重定向 ---
                $user = Auth::user();
                if ($user->hasRole('admin')) {
                    // return redirect(RouteServiceProvider::ADMIN_HOME); // 如果有定義 ADMIN_HOME
                    return redirect()->route('admin.dashboard');
                } elseif ($user->hasRole('arcade-owner') || $user->hasRole('arcade-staff')) {
                    return redirect()->route('arcade.dashboard'); // 使用我們定義的路由名稱
                } elseif ($user->hasRole('machine-owner') || $user->hasRole('machine-staff')) { // 假設 machine-staff 角色存在
                    return redirect()->route('machine.dashboard'); // 使用我們定義的路由名稱
                }
                // 可以為其他角色添加更多重定向邏輯

                // 如果沒有匹配的角色，則使用預設的 HOME
                return redirect(RouteServiceProvider::HOME);
                // --- 角色重定向結束 ---
            }
        }

        return $next($request);
    }
}
