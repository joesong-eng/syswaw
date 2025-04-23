<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // API 速率限制
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // 定義路由
        $this->routes(function () {

            // 管理員專用路由
            Route::middleware(['web', 'auth', 'role:admin']) // 確保你有 'role:admin' 中間件
            ->prefix('admin') // 路由前綴
            // ->name('admin.') // 路由名稱前綴（可選）
            ->group(base_path('routes/admin.php'));
            // API 路由
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web 路由（公用）
            Route::middleware('web')
                ->group(function () {
                    // 加載 web.php
                    require base_path('routes/web.php');

                    // 加載 auth.php
                    require base_path('routes/auth.php');
                });
            });
        }
        
}