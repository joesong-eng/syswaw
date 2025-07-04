<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MachinesController;
use App\Http\Controllers\StaffController;

use App\Http\Controllers\MachineMachineKeyController; // Changed from ChipController or Admin\MachineAuthKeyController
use App\Http\Controllers\TransactionQueryController;
use App\Http\Controllers\MachineMachinesController;
use App\Http\Controllers\MonthlyReportController;

Route::prefix('monthly-reports')->name('monthly-reports.')->group(function () {
    Route::get('/', [MonthlyReportController::class, 'index'])->name('index');
    Route::get('/{report}', [MonthlyReportController::class, 'show'])->name('show');
});

// Machine Dashboard
Route::get('/dashboard', fn() => view('machine.dashboard'))->name('dashboard'); // name 會是 machine.dashboard
Route::middleware(['role:machine-owner'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/', [StaffController::class, 'index'])->name('index');
    Route::post('/', [StaffController::class, 'store'])->name('store');
    Route::get('/{user}/edit', [StaffController::class, 'edit'])->name('edit');
    Route::put('/{user}', [StaffController::class, 'update'])->name('update');
    Route::delete('/{user}', [StaffController::class, 'destroy'])->name('destroy');
    Route::post('/{user}/deactivate', [StaffController::class, 'deactivate'])->name('deactivate');
    Route::post('/invitation/generate/{user}', [StaffController::class, 'generateInvitationCode'])->name('generate.invitation');
});
// Manage Machines
Route::prefix('machines')->name('machines.')->group(function () {
    Route::get('/', [MachineMachinesController::class, 'index'])->name('index');
    Route::post('/', [MachineMachinesController::class, 'store'])->name('store');
    // Route::post('/', [MachineMachinesController::class, 'destroy'])->name('destroy');

    Route::prefix('{machine}')->where(['machine' => '[0-9]+'])->group(function () {
        Route::patch('/', [MachineMachinesController::class, 'update'])->name('update');
        Route::delete('/', [MachineMachinesController::class, 'destroy'])->name('destroy');
        Route::patch('/toggle-active', [MachineMachinesController::class, 'toggleActive'])->name('toggleActive');
    });
});

Route::prefix('auth-keys')->name('auth_keys.')->group(function () {
    Route::get('/', [MachineMachineKeyController::class, 'index'])->name('index');
    Route::post('/', [MachineMachineKeyController::class, 'store'])->name('store');
    Route::post('/generate-single', [MachineMachineKeyController::class, 'generateSingleKey'])->name('generate_single');
    Route::post('/print', [MachineMachineKeyController::class, 'printKeys'])->name('print');
    Route::delete('/{key}', [MachineMachineKeyController::class, 'destroy'])->name('destroy')->where(['key' => '[0-9]+']);
});

// Transaction Query
Route::get('/transactions', [TransactionQueryController::class, 'index'])->name('transactions.index'); // Controller 內需處理權限
