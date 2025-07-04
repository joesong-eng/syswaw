@props([
    'name' => '',
    'label' => '',
    'required' => false,
    'placeholder' => '',
    'model' => '',
    'type' => 'text', // input 類型（text/select/email等）
    'options' => [], // select 專用：選項數組
    'hasButton' => false,
    'buttonAction' => '',
    'buttonText' => 'Generate',
    'buttonLoadingText' => 'Generating...',
    'isLoading' => false,
    'prefix' => '', // 新增：前綴文字
    'unit' => '', // 新增：後綴單位
    'layout' => 'vertical', // vertical | horizontal
])

<label for="{{ $name }}"
    {{ $attributes->merge(['class' => 'flex items-center space-x-2 text-sm text-gray-700 ']) }}>
    {{ __($label) }} @if ($required)
        <span class="text-red-500">*</span>
    @endif
</label>

@if ($prefix)
    <span>{{ __($prefix) }}</span>
@endif
@if ($type === 'select')
    <!-- Select 下拉框 -->
    <select name="{{ $name }}" id="{{ $name }}" x-model="{{ $model }}"
        @if ($required) required @endif
        class="mt-1 block w-full rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
        {{ $attributes }}>
        @foreach ($options as $key => $value)
            <option value="{{ $key }}">{{ __($value) }}</option>
        @endforeach
    </select>
@else
    <!-- Input 輸入框 -->
    <div class="relative">
        <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
            x-model="{{ $model }}" @if ($required) required @endif
            class="w-full mt-1 block rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @if ($hasButton) pr-24 @endif"
            placeholder="{{ __($placeholder) }}" {{ $attributes }}>
        @if ($hasButton)
            <button type="button" @click="{{ $buttonAction }}" :disabled="{{ $isLoading }}"
                class="absolute top-0 right-0 h-full px-3 py-2 text-xs font-medium text-white bg-green-500 rounded-r-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50">
                <span x-show="!{{ $isLoading }}">{{ __($buttonText) }}</span>
                <span x-show="{{ $isLoading }}">{{ __($buttonLoadingText) }}</span>
            </button>
        @endif
    </div>
@endif
@if ($layout === 'horizontal')
    </div>
@endif
@error($name)
    <span class="text-xs text-red-500">{{ $message }}</span>
@enderror
