{{-- resources/views/components/modal/sidebar-link.blade.php --}}

@props(['route', 'icon', 'title', 'description'])

@php
    // 將判斷邏輯抽出來，讓 class 屬性更乾淨
    $isActive = request()->routeIs($route . '*') || request()->routeIs($route);
@endphp

<a href="{{ route($route) }}"
    class="flex items-center w-full px-3 py-2 my-1 transition-all duration-200 rounded-lg group
   {{-- Active / Inactive 狀態的樣式 --}}
   @if ($isActive) {{-- Active: 使用更鮮豔的背景和 ring 來突顯 --}}
       bg-indigo-100 ring-2 ring-indigo-500
   @else
       {{-- Inactive: 滑鼠懸停時才變色 --}}
       hover:bg-gray-100 @endif
">
    {{-- 圖示：根據是否 Active 改變顏色 --}}
    <x-dynamic-component :component="'icon.' . $icon" @class([
        'h-6 w-6 flex-shrink-0',
        'text-indigo-600' => $isActive,
        'text-gray-500 group-hover:text-gray-700' => !$isActive,
    ]) />

    <div class="overflow-hidden pl-3">
        {{-- 標題：根據是否 Active 改變顏色 --}}
        <p @class([
            'text-sm font-semibold',
            'text-indigo-800' => $isActive,
            'text-gray-800' => !$isActive,
        ])>
            {{ $title }}
        </p>
        {{-- 描述：根據是否 Active 改變顏色 --}}
        <small @class([
            'text-xs',
            'text-gray-600' => $isActive,
            'text-gray-500' => !$isActive,
        ])>
            {{ $description }}
        </small>
    </div>
</a>
