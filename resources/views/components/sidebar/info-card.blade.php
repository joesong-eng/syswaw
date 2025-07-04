{{-- resources/views/components/sidebar/info-card.blade.php --}}

{{--
  這個元件提供一個標準化的卡片容器，
  自動處理亮色/暗色模式的背景和邊框。
--}}
<div
    {{ $attributes->merge([
        'class' => 'p-3 mb-3 rounded-lg bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm',
    ]) }}>
    {{ $slot }}
</div>
