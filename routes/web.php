<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Mail;
use App\Events\UserRequestedAdminRole;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChipController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ArcadesController;
use App\Http\Controllers\MachinesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionQueryController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

use Illuminate\Support\Facades\Redis; // 確保這行存在
Route::middleware(['setLocale', 'auth'])->name('admin.')->group(function () {
    Route::prefix('monthly-reports')->name('monthly-reports.')->group(function () {
        // 報表列表頁
        Route::get('/', [MonthlyReportController::class, 'index'])->name('index');
        // 報表詳情頁
        Route::get('/{report}', [MonthlyReportController::class, 'show'])->name('show');
    });
});
// 報表系統 (任何登入用戶都可訪問)
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index'); // 名稱: reports.index
    Route::post('/generate', [ReportController::class, 'generate'])->name('generate');
    Route::post('/print', [ReportController::class, 'print'])->name('print');
    Route::post('/export-csv', [ReportController::class, 'exportCsv'])->name('csv');
});
Route::get('/test-redis-client', function () {
    $redis = Redis::connection(); // 獲取默認 Redis 連線
    $client = $redis->client();

    if (class_exists(\Redis::class) && $client instanceof \Redis) {
        try {
            $info = $client->info();
            $version = $info['redis_version'] ?? 'N/A';
            return "Redis client is **phpredis**! Version: {$version}";
        } catch (\RedisException $e) {
            return "Redis client is phpredis, but failed to get info: " . $e->getMessage();
        }
    } else {
        return "Redis client is NOT phpredis, or an unexpected client type: " . get_class($client);
    }
});

Route::get('/test-env', function () {
    return response()->json(
        config('syswaw.tcp_api_key'), // env('TCP_API_KEY'),
    );
});
Route::get('lang/{lang}', function ($lang) { // *** 語言切換路由 ***
    if (in_array($lang, ['zh-TW', 'zh-CN', 'en'])) {
        session(['locale' => $lang]);
        app()->setLocale($lang);
    }
    return redirect()->back();
});
Route::get('/current-time', function () { //時間刷新
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
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
    });
    Route::resource('user', StaffController::class);

    // *** 各角色儀表板路由 ***
    Route::middleware(['auth', 'setLocale'])->group(function () { // 確保 setLocale 也應用於此組
        Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
        Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
        // // 機器管理相關路由 (大部分已移至 machine.php)
        Route::prefix('machines')->name('machines.')->group(function () {
            Route::patch('/update/{machine}', [MachinesController::class, 'update'])->name('update'); // 這個可能 admin 和 arcade 都需要，需確認
        });
    });
    Route::post('/arcades/upload-image', [ArcadesController::class, 'uploadImage'])->name('arcade.upload.image');

    Route::get('arcade/create', function () {
        return view('arcade.create');
    })->name('arcade.create');
    Route::get('bind_arcade', [ArcadesController::class, 'bindArcade'])->name('bind_arcade');

    Route::get('/resend-verification', function () {
        $user = Auth::user();
        if ($user instanceof User && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
            return '驗證郵件已發送！';
        }
        return '請先登入';
    });
    // Auth::routes(['verify' => true]);

    Route::get('/test-email', function () {
        $details = ['title' => '測試郵件', 'body' => '這是一封測試郵件'];
        Mail::raw('This is a test email', function ($message) {
            $message->to('songyoudo63@gmail.com')->subject('Test Email');
        });
        return '郵件已發送！';
    });
    Route::get('/test-error', function () {
        return redirect('/')->with('error', '測試錯誤訊息');
    });

    Route::get('/layout', function () {
        // 確保傳遞 title 變數，如果佈局需要的話
        return view('test.responsive-test', ['title' => 'Responsive Layout Test']);
    })->name('test.responsive'); // 給它一個名字方便查找

});
