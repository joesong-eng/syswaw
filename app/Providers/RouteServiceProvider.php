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

            // API 路由
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Web 路由
            Route::middleware(['web', 'setLocale']) // setLocale 中間件應該在此應用
                ->group(function () {
                    require base_path('routes/web.php'); // 直接 require web.php
                    require base_path('routes/auth.php'); // 直接 require auth.php
                });

            // 管理員專用路由
            Route::middleware(['web', 'auth', 'role:admin'])
                ->prefix('admin')
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Arcade 路由
            Route::middleware(['web', 'auth', 'role:arcade-owner|arcade-staff', 'setLocale'])
                ->prefix('arcade')
                ->name('arcade.')
                ->group(base_path('routes/arcade.php'));

            // Machine 路由
            Route::middleware(['web', 'auth', 'role:machine-owner|machine-staff', 'setLocale'])
                ->prefix('machine')
                ->name('machine.')
                ->group(base_path('routes/machine.php'));
        });
    }
}
