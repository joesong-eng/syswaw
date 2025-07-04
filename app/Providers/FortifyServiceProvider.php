<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 自定義登入行為
        Fortify::authenticateUsing(function ($request) {
            $user = Auth::attempt([
                'email' => $request->email,
                'password' => $request->password,
            ]);

            return $user ? Auth::user() : null;
        });

        // 登入頁面
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // 統一角色跳轉邏輯
        Fortify::redirects('login', fn() => $this->getRedirectPath());
        Fortify::redirects('email-verification', fn() => $this->getRedirectPath());

        // $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(
            \Laravel\Fortify\Contracts\UpdatesUserProfileInformation::class,
            \App\Actions\Fortify\UpdateUserProfileInformation::class
        );
    }

    /**
     * 根據角色取得跳轉路徑
     *
     * @return string
     */
    private function getRedirectPath(): string
    {
        $user = Auth::user();

        if (!$user) {
            return route('home'); // 未登入用戶重定向到首頁
        }

        if ($user->hasRole('admin')) {
            return route('admin.dashboard');
        } elseif ($user->hasRole('arcade-owner')) {
            return route('arcade.dashboard'); // 修正路由名稱
        } elseif ($user->hasRole('arcade-staff')) {
            return route('arcade.dashboard'); // 修正路由名稱
        } elseif ($user->hasRole('machine-owner')) {
            return route('machine.dashboard'); // 修正路由名稱
        } elseif ($user->hasRole('machine-manager')) {
            return route('machine.dashboard'); // 修正路由名稱
        } elseif ($user->hasRole('member')) {
            return route('member.dashboard');
        } elseif ($user->hasRole('user')) {
            return route('user.dashboard');
        }

        return route('home'); // 預設跳轉
    }
}
