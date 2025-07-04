<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale{
    public function handle(Request $request, Closure $next){
        $locale = Session::get('locale');
        if (!$locale) {
            $supportedLanguages = ['zh-TW', 'zh-CN', 'zh', 'en-US', 'en'];
            $acceptLanguage = $request->header('Accept-Language');

            $languagePreferences = explode(',', $acceptLanguage);// 將 Accept-Language 解析為陣列，處理權重
            $languageMap = [];
            foreach ($languagePreferences as $lang) {
                $parts = explode(';', $lang);
                $langCode = trim($parts[0]);
                $qValue = isset($parts[1]) ? (float) str_replace('q=', '', $parts[1]) : 1.0;
                $languageMap[$langCode] = $qValue;
            }
            arsort($languageMap);// 依據 q 值排序，q 值越高排序越前
            $browserLocale = null;
            foreach ($languageMap as $langCode => $qValue) {// 調整支援語言的優先順序
                if (in_array($langCode, $supportedLanguages)) {
                    $browserLocale = $langCode;
                    break;
                }
            }
            $browserLocale = $browserLocale ?? 'en';// 默認語言設置為 'en'
            Session::put('locale', $browserLocale);// 儲存至 Session
            $locale = $browserLocale;
        }
        App::setLocale($locale);// 設置應用的語言環境
        return $next($request);
    }
}