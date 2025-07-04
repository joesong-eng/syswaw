<!-- resources/views/arcades/index.blade.php -->
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center">
        <x-svg-icons name="store" classes="h-6 w-6 mr-2" />
        {{ __('msg.arcade_info') }}
    </h2>
@endsection
@section('content')
    <div class="flex justify-center bg-gray-100 pt-2" x-data="{
        editArcadeModal: false,
        selectedStore: {},
        updateSelectedStoreImage(detail) {
            this.selectedStore.image_url = detail.imageUrl;
            this.selectedStore.image_name = detail.imageName;
        },
        regenerateAuthCode(arcadeId, arcadeName) {
            if (!confirm('{{ __('msg.confirm_action', ['action' => __('msg.generate_new_code')]) }}' + ' \n\n 遊藝場: ' + arcadeName)) {
                return;
            }
            showLoadingOverlay();
            fetch(`{{ url('arcade') }}/${arcadeId}/regenerate-auth-code`, { // Construct URL manually
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoadingOverlay();
                    if (data.success) {
                        // Update the display on the card
                        const authCodeDisplayElement = document.getElementById(`auth_code_display_${arcadeId}`);
                        if (authCodeDisplayElement) {
                            authCodeDisplayElement.textContent = data.authorization_code;
                        }
                        // If this arcade is currently selected in the modal, update it too
                        if (this.selectedStore.id === arcadeId) {
                            this.selectedStore.authorization_code = data.authorization_code;
                        }
                        Swal.fire({ icon: 'success', title: '{{ __('msg.success') }}', text: data.message });
                    } else {
                        Swal.fire({ icon: 'error', title: '{{ __('msg.error') }}', text: data.message });
                    }
                })
                .catch(error => {
                    hideLoadingOverlay();
                    console.error('Error:', error);
                    Swal.fire({ icon: 'error', title: '{{ __('msg.error') }}', text: '{{ __('msg.unknown_error') }}' });
                });
        }
    }"
        @update-selected-store-image.window="updateSelectedStoreImage($event.detail)">
        <div class="relative w-full max-w-2xl bg-opacity-60 px-6 overflow-scroll" style="max-height: calc(100vh - 70px);">
            @foreach ($arcades as $arcade)
                <div class="mb-4 bg-white border border-gray-200 rounded-lg md:max-w-2xl shadow-lg">
                    <div class="flex px-3 pt-3 justify-between">
                        <div class="flex justify-items-start font-thin pb-0 m-0 text-gray-700">
                            {{ __('msg.status') }}: {{ $arcade->is_active ? __('msg.active') : __('msg.inactive') }}
                        </div>
                        <div class="flex justify-items-end space-x-1">
                            <button class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition px-2 py-1"
                                @click="editArcadeModal = true; selectedStore = {
                                    id: {{ $arcade->id }},
                                    name: {{ json_encode($arcade->name) }},
                                    image_url: {{ json_encode($arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg')) }},
                                    image_name: {{ json_encode($arcade->image_url ? $arcade->image_url : 'default-store.jpg') }},
                                    address: {{ json_encode($arcade->address ?? '') }},
                                    phone: {{ json_encode($arcade->phone ?? '') }},
                                    business_hours: {{ json_encode($arcade->business_hours ?? '') }},
                                    authorization_code: {{ json_encode($arcade->authorization_code ?? '') }},
                                    currency: {{ json_encode($arcade->currency ?? 'TWD') }} // 新增 currency
                                }">
                                <x-svg-icons name="edit" classes="h-4 w-4" />
                            </button>

                            <form action="{{ route('arcade.destroy', $arcade->id) }}" method="POST" class="confirm-submit"
                                data-confirm="{{ __('msg.confirm_action', ['action' => __('msg.delete') . ' ' . $arcade->name]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="bg-red-500 text-white rounded-md hover:bg-red-600 transition p-2">
                                    <x-svg-icons name="delete" classes="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row p-4">
                        @if (Auth::user()->arcade || (Auth::user()->parent && Auth::user()->parent->arcade))
                            <div class="m-auto w-full sm:w-1/3 max-w-xs flex-shrink-0">
                                <!-- 店鋪圖片 -->
                                <img x-bind:src="selectedStore.id === {{ $arcade->id }} && selectedStore.image_url ? selectedStore
                                    .image_url :
                                    '{{ $arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg') }}'"
                                    alt="{{ $arcade->name }} {{ __('msg.image') }}"
                                    class="m-auto rounded-md object-cover w-full h-32 sm:h-40">
                            </div>
                            <div class="flex flex-col justify-between leading-normal w-full sm:w-2/3 sm:pl-4 mt-4 sm:mt-0">
                                <h3 class="text-lg font-bold text-gray-800">{{ $arcade->name }}</h3>
                                <p class="text-sm text-gray-600">{{ __('msg.address') }}:
                                    {{ $arcade->address ?? __('msg.not_set') }}</p>
                                <p class="text-sm text-gray-600">{{ __('msg.phone') }}:
                                    {{ $arcade->phone ?? __('msg.not_set') }}</p>
                                <div class="text-sm text-gray-600 mt-1">
                                    {{ __('msg.authorization_code') }}:
                                    <span id="auth_code_display_{{ $arcade->id }}"
                                        class="font-mono bg-gray-200 px-1 rounded">{{ $arcade->authorization_code ?? __('msg.not_set') }}</span>
                                    @if ($arcade->authorization_code)
                                        <button
                                            class="ml-2 px-1 py-0.5 bg-blue-500 text-white rounded text-xs hover:bg-blue-600"
                                            onclick="copyToClipboard('{{ $arcade->authorization_code }}', 'auth_code_display_{{ $arcade->id }}_copy_feedback_{{ $arcade->id }}')">
                                            <i class="bi bi-clipboard h-3 w-3"></i>
                                        </button>
                                        <span id="auth_code_display_{{ $arcade->id }}_copy_feedback_{{ $arcade->id }}"
                                            class="text-xs"></span>
                                    @endif
                                    <button
                                        class="ml-1 px-1 py-0.5 bg-orange-500 text-white rounded text-xs hover:bg-orange-600"
                                        title="{{ __('msg.regenerate') }} {{ __('msg.authorization_code') }}"
                                        @click="regenerateAuthCode({{ $arcade->id }}, {{ json_encode($arcade->name) }})">
                                        <i class="bi bi-arrow-repeat h-5 w-5"></i>
                                    </button>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ __('msg.business_hours') }}:
                                    {{ $arcade->business_hours ?? __('msg.not_set') }}</p>
                            </div>
                        @else
                            <div class="flex flex-col justify-between p-4 leading-normal w-full">
                                <p class="text-gray-700">
                                    {{ __('msg.no_arcade_info') }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- 編輯遊藝場模態框 -->
        <div x-cloak x-show="editArcadeModal" class="fixed inset-0 z-50 overflow-y-auto" x-data="{
            uploadedImage: '', // 用於預覽的圖片 URL
            imageNameForForm: '', // 用於表單提交的圖片文件名
            init() {
                this.$watch('selectedStore', value => {
                    if (value && value.id !== undefined) {
                        this.uploadedImage = value.image_url; // 完整 URL
                        this.imageNameForForm = value.image_name; // 文件名
                        // Clear previous messages
                        document.getElementById('arcade_message').innerHTML = '';
                        document.getElementById('arcade_notemsg').innerHTML = '';
                    } else {
                        this.uploadedImage = '{{ Storage::url('images/default-store.jpg') }}';
                        this.imageNameForForm = 'default-store.jpg';
                    }
                });
            },
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.uploadedImage = URL.createObjectURL(file);
                    document.getElementById('arcade_notemsg').innerHTML = '{{ __('msg.confirm_then_upload') }}';
                    document.getElementById('arcade_message').innerHTML = ''; // Clear previous upload status
                } else {
                    // 如果沒有選擇文件，恢復到 selectedStore 中的圖片或預設圖片
                    this.uploadedImage = this.selectedStore.image_url ? this.selectedStore.image_url : '{{ Storage::url('images/default-store.jpg') }}';
                    document.getElementById('arcade_notemsg').innerHTML = '';
                }
            },
            uploadImage() {
                let form = document.getElementById('arcade_uploadForm');
                let fileInput = document.getElementById('arcade_image_upload_input');
                if (!fileInput.files || fileInput.files.length === 0) {
                    document.getElementById('arcade_message').innerHTML = '{{ __('msg.please_select_image_first') }}';
                    return;
                }
                let formData = new FormData(form);
                showLoadingOverlay();
                fetch('{{ route('arcade.upload.image') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(err => { throw err; }); // Throw error object for better handling
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('arcade_message').innerHTML = `<span class='text-green-500'>${data.message}</span>`;
                            this.uploadedImage = '{{ Storage::url('images/') }}' + data.image_path;
                            this.imageNameForForm = data.image_path;
                            this.$dispatch('update-selected-store-image', { imageUrl: this.uploadedImage, imageName: data.image_path });
                            document.getElementById('arcade_notemsg').innerHTML = ''; // Clear note message
                        } else {
                            document.getElementById('arcade_message').innerHTML = `<span class='text-red-500'>${data.message || '{{ __('msg.upload_failed') }}'}</span>`;
                        }
                    })
                    .catch(error => {
                        let errorMessage = '{{ __('msg.upload_failed') }}';
                        if (error && error.message) {
                            errorMessage += `: ${error.message}`;
                        }
                        if (error && error.errors && error.errors.image) { // Handle validation errors
                            errorMessage = error.errors.image.join(', ');
                        }
                        document.getElementById('arcade_message').innerHTML = `<span class='text-red-500'>${errorMessage}</span>`;
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        hideLoadingOverlay();
                    });
            }
        }">
            <div class="absolute inset-0 bg-black bg-opacity-50" @click="editArcadeModal = false"></div>
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg" @click.stop>
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">
                        {{ __('msg.edit') }}{{ __('msg.arcade_info') }}</h2>

                    <form id="arcade_uploadForm" enctype="multipart/form-data" @submit.prevent="uploadImage">
                        @csrf
                        <div class="mt-4">
                            <img :src="uploadedImage" alt="{{ __('msg.arcade_image_preview') }}"
                                class="m-auto rounded-md object-cover w-full h-40">
                        </div>
                        <div id="arcade_notemsg" class="mt-2 text-sm text-yellow-600 text-center">
                        </div>
                        <div class='flex space-x-2 items-center justify-between px-3 mt-2'>
                            <div class="flex-grow">
                                <label for="arcade_image_upload_input" class="sr-only">{{ __('msg.upload_image') }}</label>
                                <input type="file" name="image" id="arcade_image_upload_input"
                                    class="mt-1 block w-full text-sm text-gray-700
                                           file:mr-4 file:py-2 file:px-4
                                           file:rounded-full file:border-0
                                           file:text-sm file:font-semibold
                                           file:bg-blue-50
                                           file:text-blue-700
                                           hover:file:bg-blue-100"
                                    @change="previewImage" accept="image/jpeg,image/png,image/gif,image/webp">
                            </div>
                            <button type="submit" title="{{ __('msg.upload_selected_image') }}"
                                class="max-h-10 bg-blue-500 hover:bg-blue-700 text-white font-bold p-2 rounded-md">
                                <x-icon.upload class='h-5 w-5' />
                            </button>
                        </div>
                        <div id="arcade_message" class="mt-2 text-sm text-center"></div>
                    </form>

                    <hr class="my-4 border-gray-300">

                    <form :action="`{{ route('arcade.update', '') }}/${selectedStore.id}`" method="POST"
                        class="confirm-submit" data-confirm="{{ __('msg.confirm_save_changes') }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="image_url" x-model="imageNameForForm">

                        <div class="mb-4">
                            <label for="arcade_name"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.name') }}</label>
                            <input type="text" id="arcade_name" name="name" x-model="selectedStore.name" required
                                class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="arcade_address"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.address') }}</label>
                            <input type="text" id="arcade_address" name="address" x-model="selectedStore.address"
                                class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="arcade_phone"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.phone') }}</label>
                            <input type="text" id="arcade_phone" name="phone" x-model="selectedStore.phone"
                                class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="arcade_business_hours"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.business_hours') }}</label>
                            <input type="text" id="arcade_business_hours" name="business_hours"
                                x-model="selectedStore.business_hours"
                                class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="mb-4">
                            <label for="arcade_currency"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.currency') }}</label>
                            <select id="arcade_currency" name="currency" x-model="selectedStore.currency" required
                                class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                @foreach (config('bill_mappings', []) as $code => $displayNameKey)
                                    <option value="{{ $code }}">
                                        {{ __('msg.' . $code) }}
                                        ({{ __($code) }})
                                    </option>
                                @endforeach
                                {{-- 您可以根據需要添加更多幣種，或者從控制器傳遞一個幣種列表 --}}
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="arcade_authorization_code"
                                class="block text-sm font-medium text-gray-700">{{ __('msg.authorization_code') }}</label>
                            <div class="flex items-center space-x-2">
                                <input type="text" id="arcade_authorization_code" name="authorization_code"
                                    x-model="selectedStore.authorization_code" readonly
                                    class="flex-grow border px-2 py-1.5 rounded-md bg-gray-100 cursor-not-allowed">
                                <button type="button"
                                    title="{{ __('msg.regenerate') }} {{ __('msg.authorization_code') }}"
                                    @click="regenerateAuthCode(selectedStore.id, selectedStore.name)"
                                    class="p-1.5 bg-orange-500 text-white rounded-md hover:bg-orange-600 transition">
                                    <i class="bi bi-arrow-repeat h-5 w-5"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('msg.auth_code_note_arcade_page') }}</p>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-md"
                                @click="editArcadeModal = false">
                                {{ __('msg.cancel') }}
                            </button>
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-md">
                                {{ __('msg.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@php
    $title = __('msg.arcade_info');
@endphp
