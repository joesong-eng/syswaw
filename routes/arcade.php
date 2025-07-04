<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArcadesController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ArcadeMachinesController; // 引入新的 Controller
use App\Http\Controllers\TransactionQueryController;
use App\Http\Controllers\ChipController; // 保留，可能其他地方還用到
use App\Http\Controllers\ArcadeMachineKeyController; // 引入新的 Controller
use App\Http\Controllers\MonthlyReportController;


Route::prefix('monthly-reports')->name('monthly-reports.')->group(function () {
    Route::get('/', [MonthlyReportController::class, 'index'])->name('index');
    Route::get('/{report}', [MonthlyReportController::class, 'show'])->name('show');
});
// 這個檔案中的所有路由都會自動應用 'web', 'auth', 'role:arcade-owner|arcade-staff', 'setLocale' 中間件
// 以及 'arcade' 前綴 (在 RouteServiceProvider 中定義)

// Arcade Dashboard
Route::get('/dashboard', fn() => view('arcade.dashboard'))->name('dashboard'); // name 會是 arcade.dashboard

// Manage Arcades (Leveraging existing ArcadesController)
// Routes for the Arcade entity itself
Route::get('/', [ArcadesController::class, 'index'])->name('index');
// Group operations for a specific arcade
Route::prefix('{arcade}')->where(['arcade' => '[0-9]+'])->group(function () { // Ensure {arcade} is numeric if it's an ID
    Route::get('/edit', [ArcadesController::class, 'edit'])->name('edit');
    Route::put('/', [ArcadesController::class, 'update'])->name('update'); // For updating arcade details
    Route::delete('/', [ArcadesController::class, 'destroy'])->name('destroy'); // For deleting an arcade
    Route::patch('/toggle-active', [ArcadesController::class, 'toggleActive'])->name('toggleActive'); // For toggling arcade active status
    // Route::post('/upload-image', [ArcadesController::class, 'uploadImage'])->name('uploadImage'); // This route is defined globally in web.php, consider moving or ensuring it's correct here if scoped to arcade
    Route::post('/regenerate-auth-code', [ArcadesController::class, 'regenerateAuthCode'])->name('regenerateAuthCode');
});

// Staff Management (Leveraging existing StaffController, but scoped to owner)
Route::middleware(['role:arcade-owner'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->name('index'); // Controller 內需處理 scope
    Route::post('/', [StaffController::class, 'store'])->name('store'); // Controller 內需設定 parent_id
    Route::get('/{user}/edit', [StaffController::class, 'edit'])->name('edit'); // 需要對應的 view
    Route::put('/{user}', [StaffController::class, 'update'])->name('update'); // 處理權限更新
    Route::delete('/{user}', [StaffController::class, 'destroy'])->name('destroy'); // Controller 內需檢查 parent_id
    //遊藝場場主或遊戲機機主給員工新贈帳戶的邀請碼
    Route::post('/invitation/generate/{user}', [StaffController::class, 'generateInvitationCode'])->name('generate.invitation'); // 邀請碼
    Route::post('/{user}/deactivate', [StaffController::class, 'deactivate'])->name('deactivate'); // Controller 內需檢查 parent_id
});

// Arcade Owner/Staff 的機器管理路由
Route::prefix('machines')->name('machines.')->group(function () {
    Route::get('/', [ArcadeMachinesController::class, 'index'])->name('index');
    Route::post('/', [ArcadeMachinesController::class, 'store'])->name('store');

    Route::prefix('{machine}')->where(['machine' => '[0-9]+'])->group(function () { // Ensure {machine} is numeric if it's an ID
        // Edit is typically handled by a modal, so a dedicated GET /edit route might not be needed
        // Route::get('/edit', [ArcadeMachinesController::class, 'edit'])->name('edit');
        Route::patch('/', [ArcadeMachinesController::class, 'update'])->name('update');
        Route::delete('/', [ArcadeMachinesController::class, 'destroy'])->name('destroy');
        Route::patch('/toggle-active', [ArcadeMachinesController::class, 'toggleActive'])->name('toggleActive');
    });
});

// 其他 Arcade Owner/Staff 相關路由 (例如：交易查詢、機器管理等)
Route::get('/transactions', [TransactionQueryController::class, 'index'])->name('transactions.index');
// Route::get('/machines', [MachinesController::class, 'index'])->name('machines.index'); // 舊的共用路由


// 管理 Machine Auth Keys (取代之前的 /chips)
Route::prefix('auth-keys')->name('auth_keys.')->group(function () { // Standardized prefix
    Route::get('/', [ArcadeMachineKeyController::class, 'index'])->name('index');
    Route::post('/', [ArcadeMachineKeyController::class, 'store'])->name('store'); // 加入生成金鑰的路由
    Route::post('/generate-single', [ArcadeMachineKeyController::class, 'generateSingleKey'])->name('generate_single'); // 新增生成單一金鑰路由
    Route::post('/print', [ArcadeMachineKeyController::class, 'printKeys'])->name('print'); // 加入列印路由

    Route::prefix('{key}')->where(['key' => '[0-9]+'])->group(function () { // Ensure {key} is numeric if it's an ID
        // Route::get('/edit', [ArcadeMachineKeyController::class, 'edit'])->name('edit'); // No edit method in controller
        // Route::put('/', [ArcadeMachineKeyController::class, 'update'])->name('update'); // No update method in controller
        Route::delete('/', [ArcadeMachineKeyController::class, 'destroy'])->name('destroy'); // 加入刪除路由 (參數名是 key)
    });
});

// Arcade Statistics
Route::get('/statistics', [ArcadesController::class, 'statistics'])->name('statistics.index');
