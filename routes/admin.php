<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ArcadesController;
use App\Http\Controllers\MachinesController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Tcp\TcpServerController;
use App\Http\Controllers\EtherealController;

Route::middleware(['setLocale', 'auth'])->group(function () {
    Route::prefix('tcp-server')->group(function () {
        Route::get('/', [TcpServerController::class, 'index'])->name('admin.tcp-server');
        Route::post('/open-data-gate', [TcpServerController::class, 'openDataGate'])->name('admin.tcp-server.openDataGate'); // 這裡可以給一個路由名稱，方便以後生成 URL
        Route::post('/restart', [TcpServerController::class, 'restart'])->name('admin.tcp-server.restart');
        Route::post('/start', [TcpServerController::class, 'start'])->name('admin.tcp-server.start');
        Route::post('/stop', [TcpServerController::class, 'stop'])->name('admin.tcp-server.stop');
        Route::post('/query', [TcpServerController::class, 'query'])->name('admin.tcp-server.query');
        Route::get('/stream', [TcpServerController::class, 'stream']);
        Route::get('/status', [TcpServerController::class, 'getCurrentStatus']);
    });
    Route::get('/ethereal', [EtherealController::class, 'index']);
    Route::post('/ethereal/broadcast', [EtherealController::class, 'xbroadcast'])->name('ethereal.broadcast');
    Route::post('/ethereal/event', [EtherealController::class, 'xevent']);
    Route::post('/ethereal/redis_ctrl', [EtherealController::class, 'redis_ctrl'])->name('ethereal.redis_ctrl');


    Route::resource('roles', RoleController::class)->except(['destroy', 'update']);
    Route::middleware('role:admin')->group(function () {
        Route::delete('/roles/del/{role}', [RoleController::class, 'destroy'])->name('admin.roles.destroy');
        Route::put('/roles/update/{role}', [RoleController::class, 'update'])->name('admin.roles.update');
        // Route::resource('users', UserController::class);
        Route::get('users', [UserController::class, 'index'])->name('admin.users');
        Route::post('users', [UserController::class, 'store'])->name('admin.users.store');
        Route::delete('/user/del/{user}', [UserController::class, 'destroy'])->name('admin.user.destroy');
        Route::put('/user/update/{user}', [UserController::class, 'update'])->name('admin.user.update');
        Route::post('/user/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
        Route::post('/users/search', [UserController::class, 'search'])->name('admin.user.search');
        Route::post('/users/{user}/verify', [UserController::class, 'verifyEmail'])->name('admin.users.verify');

        // Route::get ('/users/search/{text}', [UserController::class, 'search'])->name('admin.user.search');
        Route::get('/dashboard', fn() => view('admin.dashboard'))->name('admin.dashboard');
        // Route::put('/admin/users/{user}', [UserController::class, 'update'])->name('admin.users.update');
        // -------- Arcade *****
        Route::get('arcades', [ArcadesController::class, 'index'])->name('admin.arcades');
        Route::get('arcade/create', [ArcadesController::class, 'create'])->name('admin.arcade.create');
        Route::post('arcade/store', [ArcadesController::class, 'store'])->name('admin.arcade.store');
        Route::patch('arcades/{id}/toggleActive', [ArcadesController::class, 'toggleActive'])->name('admin.arcade.toggleActive');
        // -------- ArcadeKey *****
        Route::get('arcadeKey', [ArcadesController::class, 'arcadeKey'])->name('admin.arcadeKey');
        Route::post('keyStore', [ArcadesController::class, 'keyStore'])->name('admin.keyStore');
        Route::delete('keyDestroy/{id}', [ArcadesController::class, 'keyDestroy'])->name('arcade.keyDestroy');
        // -------- machines *****
        Route::get('machines', [MachinesController::class, 'index'])->name('admin.machines');
        Route::patch('machine/update/{id}', [MachinesController::class, 'update'])->name('admin.machine.update');        // Route::patch('/machine/update/{id}', [MachinesController::class, 'update'])->name('admin.machine.update');
        Route::get('/machine/edit/{machine}', [MachinesController::class, 'edit'])->name('admin.machine.edit');
        Route::delete('/machine/destroy/{machine}', [MachinesController::class, 'destroy'])->name('admin.machine.destroy');
        Route::post('avtiveMachine', [MachinesController::class, 'avtiveMachine'])->name('admin.avtiveMachine');
        Route::patch('/machine/toggleActive/{id}', [MachinesController::class, 'toggleActive'])->name('machine.toggleActive');
    });
});

        // Route::post('connectMachineUpdate', [MachinesController::class, 'connectMachineUpdate'])->name('admin.connectMachineUpdate');
        // Route::get('machineKeys', [MachinesController::class, 'machineKeys_admin'])->name('admin.machineKey');
        // Route::post('addMachinekey', [MachinesController::class, 'addMachineKey'])->name('machine.addMachinekey');
        // Route::patch('arcades/{id}/update', [ArcadesController::class, 'update'])->name('admin.arcades.update');
        // Route::get('newArcade', [ArcadesController::class, 'newArcade'])->name('admin.newArcade');
        // Route::put( 'arcadeUpdate/{arcade}', [ArcadesController::class, 'arcadeUpdate'])->name('arcade.update');
        // Route::get('arcades', [ArcadesController::class, 'arcades'])->name('admin.arcades');

        // -------- TCP *****
        // Route::get('/tcp', [TcpServiceController::class, 'index'])->name('admin.tcp.service');
        // Route::get( '/onlineMachines', [TcpServiceController::class, 'onlineMachines'])->name('admin.online');
        // Route::post('/onlineMachines', [TcpServiceController::class, 'onlineMachines'])->name('admin.online');