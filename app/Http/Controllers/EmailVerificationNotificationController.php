<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\EmailLog; // 假設您已創建 EmailLog 模型
use Symfony\Component\Mailer\Exception\UnexpectedResponseException;
use App\Providers\RouteServiceProvider;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // 檢查用戶是否已驗證
        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        // 檢查每日發送限制（例如 400 封）
        $dailyLimit = 400;
        $todayCount = EmailLog::whereDate('created_at', today())->count();
        if ($todayCount >= $dailyLimit) {
            Log::warning('Daily email limit reached', ['count' => $todayCount]);
            return redirect()->route('login')->withErrors(['email' => '由於系統郵件發送量過大，目前無法發送驗證郵件，請稍後重試。']);
        }

        // 限制單用戶發送頻率（每小時一次）
        $cacheKey = 'email_verification_' . $user->id;
        if (Cache::has($cacheKey)) {
            Log::warning('Verification email rate limit exceeded', ['user_id' => $user->id]);
            return back()->withErrors(['email' => '驗證郵件發送過於頻繁，請稍後重試。']);
        }

        // 記錄發送日誌
        Log::info('Preparing to send verification email', [
            'user_id' => $user->id,
            'email' => $user->email,
            'timestamp' => now()->toDateTimeString(),
        ]);

        try {
            $user->sendEmailVerificationNotification();

            // 記錄成功的發送
            EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => 'verification',
                'success' => true,
            ]);

            // 設置冷卻時間（1 小時）
            Cache::put($cacheKey, true, now()->addHour());

            return back()->with('status', 'verification-link-sent'); // 使用 session flash data
        } catch (UnexpectedResponseException $e) {
            // 記錄失敗的發送
            EmailLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'type' => 'verification',
                'success' => false,
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            // 檢查是否是每日發送限制的錯誤
            if (str_contains($e->getMessage(), 'Daily user sending limit exceeded')) {
                return redirect()->route('login')->withErrors(['email' => '由於系統郵件發送量已達上限，目前無法發送驗證郵件，請稍後重試。']);
            }

            // 如果是其他 UnexpectedResponseException，導回上一頁並顯示通用錯誤
            return back()->withErrors(['email' => '無法發送驗證郵件，請聯繫管理員。']);
        }
    }
}
