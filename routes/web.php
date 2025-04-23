<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Events\UserRequestedAdminRole;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChipController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ArcadesController;
use App\Http\Controllers\MachinesController;
use App\Http\Controllers\TransactionQueryController;
use Illuminate\Support\Facades\Auth;
Route::get('/test-env', function () {
    return response()->json([
        'TCP_API_KEY' => config('tcp_api_key'),// env('TCP_API_KEY'),
    ]);
});
Route::get('lang/{lang}', function ($lang) {// *** 語言切換路由 ***
    if (in_array($lang, [ 'zh-TW', 'zh-CN', 'en'])) {
        session(['locale' => $lang]);
        app()->setLocale($lang);
    }
    return redirect()->back();
});
Route::get('/current-time', function () {//時間刷新
    date_default_timezone_set('Asia/Taipei');
    return date('ymd H:i');
});
Route::middleware(['setLocale'])->group(function () {
    Route::get('/', function () {
        if (auth()->check()) {
            return redirect()->route('dashboard')->with('error', session('error'));
        }
        return view('welcome');
    })->name('home');

    Route::middleware([
        'auth:sanctum', config('jetstream.auth_session'),'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard'); })->name('dashboard');
    });
    Route::resource('user', StaffController::class);

    Route::get('/transactions', [TransactionQueryController::class, 'index'])->name('transactions.index');

    // *** 各角色儀表板路由 ***
    Route::middleware(['auth'])->group(function () {
        Route::get('/admin/dashboard', fn() => view('admin.dashboard'))->name('admin.dashboard');
        Route::get('/arcade/dashboard', fn() => view('arcade.dashboard'))->name('arcades.dashboard');
        Route::get('/machine/dashboard', fn() => view('machine.dashboard'))->name('machine.dashboard');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        Route::post('printKeys', [ChipController::class, 'printKeys'])->name('printKeys');
        
        Route::prefix('arcades')->group(function () {
            Route::get('/', [ArcadesController::class, 'index'])->name('arcades.index');
            Route::put('update/{id}', [ArcadesController::class, 'update'])->name('arcades.update');
            Route::post('store', [ArcadesController::class, 'store'])->name('arcade.store');
            Route::post('upload-image', [ArcadesController::class, 'upload'])->name('upload.image');
            Route::get('/{key}', [ArcadesController::class, 'bindArcade'])->name('arcade.bindArcade');
            Route::delete('/{key}', [ArcadesController::class, 'destroy'])->name('arcade.destroy');
            Route::post('/invitation/generate/{user}', [StaffController::class, 'generateInvitationCode'])->name('arcade.generate.invitation');

        });
        Route::get('chips', [ChipController::class, 'index'])->name('chips.index');
        Route::post('chip/add', [ChipController::class, 'store'])->name('chip.store');
        Route::delete('chip/delete/{id}', [ChipController::class, 'destroy'])->name('chip.destroy');

        // 機器管理相關路由
        Route::prefix('machines')->name('machine.')->group(function () {
            Route::get('/', [MachinesController::class, 'index'])->name('index');
            Route::post('/store', [MachinesController::class, 'store'])->name('store');
            Route::get('/edit/{machine}', [MachinesController::class, 'edit'])->name('edit');
            Route::delete('/destroy/{machine}', [MachinesController::class, 'destroy'])->name('destroy');
            Route::post('/activate/{machine}', [MachinesController::class, 'activate'])->name('activate');
            Route::patch('/toggleActive/{id}', [MachinesController::class, 'toggleActive'])->name('toggleActive');
            // Route::patch('/update/{id}', [MachinesController::class, 'update'])->name('update');
            Route::patch('/update/{machine}', [MachinesController::class, 'update'])->name('update');
        });
        // 員工管理相關路由  
        Route::prefix('staff')->group(function () {
            Route::get('/', [StaffController::class, 'staff'])->name('staff');
            Route::post('store', [StaffController::class, 'store'])->name('staff.store');
            Route::get('edit', [StaffController::class, 'edit'])->name('staff.edit');
            Route::put('update/{user}', [StaffController::class, 'update'])->name('staff.update');
            Route::delete('del/{user}', [StaffController::class, 'destroy'])->name('staff.destroy');
            Route::post('{user}/deactivate', [StaffController::class, 'deactivate'])->name('staff.deactivate');
        });




    });

    Route::get('arcade/create', function () {return view('arcade.create'); })->name('arcade.create');
    Route::get('bind_arcade', [ArcadesController::class, 'bindArcade'])->name('bind_arcade');
    
    Route::get('/resend-verification', function () {
        if (Auth::user()) {
            Auth::user()->sendEmailVerificationNotification();
            return '驗證郵件已發送！';
        }
        return '請先登入';
    });

    Route::get('/test-email', function () {
        $details = [ 'title' => '測試郵件', 'body' => '這是一封測試郵件'];
        Mail::raw('This is a test email', function ($message) { $message->to('songyoudo63@gmail.com')->subject('Test Email'); });
        return '郵件已發送！';
    });
    Route::get('/test-error', function () {
        return redirect('/')->with('error', '測試錯誤訊息');
    });
    Route::get('/set-error', function () {
        session()->flash('error','這是一個測試錯誤訊息');
        return view('admin.');
    });
});




    


