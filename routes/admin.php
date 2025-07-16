<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminArcadesController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminMachinesController;
use App\Http\Controllers\Admin\MachineAuthKeyController;
use App\Http\Controllers\Tcp\TcpServerController;
use App\Http\Controllers\Tcp\Api\TcpStatusController;
use App\Http\Controllers\EtherealController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MonthlyReportController;
use App\Http\Controllers\DataIngestionController; // 引入新的控制器

// 所有路由都需要登入和設定語系
Route::middleware(['setLocale', 'auth', 'role:admin'])->group(function () {
    // =========================================================
    //         超級管理員 ('admin' 角色) 專用功能
    // =========================================================
    Route::prefix('monthly-reports')->name('monthly-reports.')->group(function () {
        Route::get('/', [MonthlyReportController::class, 'index'])->name('index');
        Route::get('/{report}', [MonthlyReportController::class, 'show'])->name('show');
    });

    Route::get('dashboard', fn() => view('admin.dashboard'))->name('dashboard'); // 名稱: admin.dashboard

    // --- TCP Server & Ethereal ---
    Route::prefix('tcp-server')->name('tcp-server.')->group(function () {
        Route::get('/', [DataIngestionController::class, 'index'])->name('index'); // 名稱: admin.tcp-server.index
        Route::get('/streamData', [DataIngestionController::class, 'streamData'])->name('streamData'); // 名稱: admin.tcp-server.streamData
        Route::get('/status', [TcpStatusController::class, 'getStatus'])->name('status');
        Route::post('/control', [TcpServerController::class, 'control'])->name('control');
    });

    Route::prefix('ethereal')->name('ethereal.')->group(function () {
        Route::get('/', [EtherealController::class, 'index'])->name('index');
        Route::post('/broadcast', [EtherealController::class, 'xbroadcast'])->name('broadcast');
    });

    // --- 角色管理 (Roles) ---
    // 自動生成 admin.roles.index, admin.roles.create, admin.roles.store 等
    Route::resource('roles', RoleController::class);

    // --- 用戶管理 (Users) ---
    // 自動生成 admin.users.index 等
    Route::resource('users', UserController::class)->except(['show']);

    Route::post('users/search', [UserController::class, 'search'])->name('users.search');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    Route::post('users/{user}/verify', [UserController::class, 'verifyEmail'])->name('users.verify');

    // --- 遊藝場管理 (Arcades) ---
    // 自動生成 admin.arcades.index 等
    Route::resource('arcades', AdminArcadesController::class);
    // -------- Arcade *****
    // Route::get('arcades', [AdminArcadesController::class, 'index'])->name('arcades');
    // Route::get('arcades/create', [AdminArcadesController::class, 'create'])->name('arcades.create');
    // Route::post('arcades/store', [AdminArcadesController::class, 'store'])->name('arcades.store');
    // Route::put('arcades/update/{id}', [AdminArcadesController::class, 'update'])->name('arcades.update'); // Keep as PUT for updates
    // Route::delete('arcades/destroy/{id}', [AdminArcadesController::class, 'destroy'])->name('arcades.destroy'); // Change POST to DELETE
    // Route::patch('arcades/{id}/toggleActive', [AdminArcadesController::class, 'toggleActive'])->name('arcades.toggleActive');

    Route::patch('arcades/{arcade}/toggle-active', [AdminArcadesController::class, 'toggleActive'])->name('arcades.toggleActive');

    // --- 遊藝場金鑰管理 (Arcade Keys) ---
    Route::get('arcade-keys', [AdminArcadesController::class, 'arcadeKey'])->name('arcadeKey.index'); // 名稱: admin.arcadeKey.index
    Route::post('keyStore', [AdminArcadesController::class, 'keyStore'])->name('arcadeKey.store');
    Route::delete('arcade-keys/{key}', [AdminArcadesController::class, 'keyDestroy'])->name('arcadeKey.destroy');

    // --- 機台管理 (Machines) ---
    // 自動生成 admin.machines.index 等
    Route::resource('machines', AdminMachinesController::class);
    Route::patch('machines/{machine}/toggle-active', [AdminMachinesController::class, 'toggleActive'])->name('machines.toggleActive');

    // --- 機台金鑰管理 (Machine Auth Keys) ---
    // 自動生成 admin.machine_auth_keys.index 等
    Route::resource('machine_auth_keys', MachineAuthKeyController::class);
    Route::post('machine_auth_keys/print', [MachineAuthKeyController::class, 'printKeys'])->name('machine_auth_keys.print');
    Route::post('machine_auth_keys/generate-single', [MachineAuthKeyController::class, 'generateSingleKey'])->name('machine_auth_keys.generateSingle');
    // --- 月結報表查看 ---
}); // End of admin role middleware group



Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index'); // 名稱: reports.index
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    Route::post('/print', [ReportController::class, 'print'])->name('print');
    Route::post('/export-csv', [ReportController::class, 'exportCsv'])->name('csv');
});
