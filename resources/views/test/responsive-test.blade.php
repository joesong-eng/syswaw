{{-- /Users/ilawusong/Documents/sysWawIot/sys180/resources/views/test/responsive-test.blade.php --}}
@extends('layouts.responsive') {{-- 繼承新的佈局檔案 --}}

{{-- 如果你的佈局需要 header slot --}}
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        響應式佈局測試頁
    </h2>
@endsection

@section('content')
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
            <div
                class="p-6 lg:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-medium text-gray-900">
                    主內容區域
                </h1>

                <p class="mt-4 text-gray-500 leading-relaxed">
                    這裡是主內容區域。請調整瀏覽器視窗大小來測試響應式效果。
                </p>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    在中大螢幕 (>= 768px) 上，側邊欄應該固定在左側。
                </p>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    在小螢幕 (< 768px) 上，側邊欄應該可以滑入滑出，展開時會覆蓋內容區域。 </p>
                        <p class="mt-4 text-gray-500 leading-relaxed">
                            在超大螢幕 (>= 1280px) 上，主內容區域應該有最大寬度限制並水平置中。
                        </p>
                        {{-- 可以放更多內容來測試滾動 --}}
                        <div class="mt-8 h-96 bg-gray-200 rounded flex items-center justify-center">
                            <p class="text-gray-500">更多內容區塊</p>
                        </div>
            </div>
            <div
                class="p-6 lg:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-medium text-gray-900">
                    主內容區域
                </h1>

                <p class="mt-4 text-gray-500 leading-relaxed">
                    這裡是主內容區域。請調整瀏覽器視窗大小來測試響應式效果。
                </p>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    在中大螢幕 (>= 768px) 上，側邊欄應該固定在左側。
                </p>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    在小螢幕 (< 768px) 上，側邊欄應該可以滑入滑出，展開時會覆蓋內容區域。 </p>
                        <p class="mt-4 text-gray-500 leading-relaxed">
                            在超大螢幕 (>= 1280px) 上，主內容區域應該有最大寬度限制並水平置中。
                        </p>
                        {{-- 可以放更多內容來測試滾動 --}}
                        <div class="mt-8 h-96 bg-gray-200 rounded flex items-center justify-center">
                            <p class="text-gray-500">更多內容區塊</p>
                        </div>
            </div>
            <div
                class="p-6 lg:p-8 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-medium text-gray-900">
                    主內容區域
                </h1>

                <p class="mt-4 text-gray-500 leading-relaxed">
                    這裡是主內容區域。請調整瀏覽器視窗大小來測試響應式效果。
                </p>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    在中大螢幕 (>= 768px) 上，側邊欄應該固定在左側。
                </p>
                <p class="mt-4 text-gray-500 leading-relaxed">
                    在小螢幕 (< 768px) 上，側邊欄應該可以滑入滑出，展開時會覆蓋內容區域。 </p>
                        <p class="mt-4 text-gray-500 leading-relaxed">
                            在超大螢幕 (>= 1280px) 上，主內容區域應該有最大寬度限制並水平置中。
                        </p>
                        {{-- 可以放更多內容來測試滾動 --}}
                        <div class="mt-8 h-96 bg-gray-200 rounded flex items-center justify-center">
                            <p class="text-gray-500">更多內容區塊</p>
                        </div>
            </div>
        </div>
    </div>
@endsection
