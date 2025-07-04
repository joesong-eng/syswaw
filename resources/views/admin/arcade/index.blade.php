<!-- resources/views/admin/arcade/index.blade.php -->
@extends('layouts.app')
@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('msg.arcade_management') }}
    </h2>
@endsection
@section('content')
    <div class="flex justify-center bg-gray-100" x-data="{ editArcadeModal: false, selectedStore: {}, addStoreModal: false, arcadeOwners: {{ $arcadeOwners }} }">
        <div class="relative w-full bg-white bg-opacity-60 shadow-lg rounded-lg">
            <div class="flex justify-end items-center mb-2  pt-2">
                <x-button>
                    <a href="/admin/arcades/create">{{ __('msg.create_arcade') }}</a>
                </x-button>
            </div>
            <!-- 商店列表 -->
            <div class="container mx-auto pb-3">
                <div class="bg-white rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 shadow-lg">
                        <div class="w-[19%] items-center text-center">
                            <div class="font-thin text-xs whitespace-nowrap overflow-hidden text-ellipsis">
                                {{ __('msg.platform_share_pct') }}</div>{{ __('msg.name') }}
                        </div>
                        <div class="w-[30%] items-center text-center"> {{ __('msg.address') }} </div>
                        <div class="w-[17%] items-center text-center"> {{ __('msg.owner') }} </div>
                        <div class="w-[10%] items-center text-center"> {{ __('msg.type') }} </div>
                        <div class="w-[9%] items-center text-center"> {{ __('msg.status') }} </div>
                        <div class="w-[15%] items-center text-center"> {{ __('msg.active') }} </div>
                    </div>

                    <!-- Data Rows -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)] py-2 ">
                        @foreach ($arcades as $arcade)
                            <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 py-1">
                                <div class="w-[19%] break-words text-center">
                                    <div
                                        class="ps-2 font-thin text-xs text-start whitespace-nowrap overflow-hidden text-ellipsis">
                                        {{ $arcade->share_pct ?? 'N/A' }}%</div>
                                    {{ $arcade->name }}
                                    <div
                                        class="pe-2 font-thin text-xs text-end whitespace-nowrap overflow-hidden text-ellipsis">
                                        {{ $arcade->currency ?? 'TWD' }}</div>
                                </div>
                                <div class="w-[30%] break-words text-center">{{ $arcade->address }}</div>
                                <div class="w-[17%] break-words text-center">{{ $arcade->owner->name ?? 'N/A' }}</div>
                                <div class="w-[10%] break-words text-center">{{ $arcade->type ?? 'N/A' }}</div>
                                <div class="w-[9%]  justify-items-center text-center m-auto space-x-1 border-l">
                                    <form action="{{ route('admin.arcades.toggleActive', $arcade->id) }}" method="POST"
                                        class="flex justify-items-center space-x-1"
                                        onsubmit="return confirm('Are you sure you want to {{ $arcade->is_active ? 'deactivate' : 'activate' }} this arcade?');">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $arcade->is_active ? 0 : 1 }}">
                                        <button type="submit" class="m-auto text-white transition p-1">
                                            @if ($arcade->is_active)
                                                <x-svg-icons name="statusT" classes="h-6 w-6" />
                                            @else
                                                <x-svg-icons name="statusF" classes="h-6 w-6" />
                                            @endif
                                        </button>
                                    </form>
                                </div>
                                <div class="w-[15%] break-words justify-items-center text-center m-auto space-x-1">
                                    <!-- 編輯按鈕 -->
                                    <div class="flex justify-items-between space-x-1">
                                        <x-button
                                            class="bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto"
                                            @click="editArcadeModal = true; selectedStore = {
                                                id: {{ $arcade->id }},
                                                name: '{{ $arcade->name }}',
                                                image_url: '{{ $arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg') }}',
                                                image_name: '{{ $arcade->image_url ? $arcade->image_url : 'default-store.jpg' }}',
                                                address: '{{ $arcade->address }}',
                                                phone: '{{ $arcade->phone }}',
                                                owner_name: '{{ $arcade->owner->name }}',
                                                owner_id: '{{ $arcade->owner_id }}',
                                                business_hours: '{{ $arcade->business_hours }}',
                                                currency: '{{ $arcade->currency ?? 'TWD' }}', // 新增 currency
                                                share_pct: '{{ $arcade->share_pct !== 0 ? number_format((float) $arcade->share_pct, 1) : 0 }}'

                                                {{-- revenue_split: {{ $arcade->revenue_split }} --}}
                                            }">
                                            <x-svg-icons name="edit" classes="h-4 w-4" /></x-button>
                                        <form action="{{ route('admin.arcades.destroy', $arcade->id) }}" method="POST"
                                            class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <x-button
                                                class="bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto"
                                                type="submit"
                                                onclick="return confirm('{{ __('msg.confirm_active') }} {{ __('msg.delete') }}{{ __('msg.this_machine') }}{{ __('msg.zh_ask') }}')">
                                                <x-svg-icons name="delete" classes="h-4 w-4" />
                                            </x-button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>


        <div x-cloak x-show="editArcadeModal" class="fixed inset-0 z-50" x-data="{
            uploadedImage: '',
            imageUrl: '',
            init() {
                this.$watch('selectedStore', value => {
                    this.uploadedImage = value.image_url ? value.image_url : '{{ Storage::url('images/default-store.jpg') }}';
                    this.imageUrl = value.image_name ? value.image_name : 'default-store.jpg';
                });
            },
            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    this.uploadedImage = URL.createObjectURL(file);
                    document.getElementById('notemsg').innerHTML = '確認圖片後,按鈕上傳圖片';

                    console.log(this.uploadedImage);
                } else {
                    this.uploadedImage = '{{ Storage::url('images/default-store.jpg') }}';
                }
            },
            uploadImage() {
                let form = document.getElementById('uploadForm');
                let formData = new FormData(form);
                fetch('{{ route('arcade.upload.image') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('伺服器錯誤: ' + response.status);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            document.getElementById('message').innerHTML = data.message;
                            this.uploadedImage = '{{ Storage::url('images/') }}' + data.image_path;
                            this.imageUrl = data.image_path;
                            selectedStore.image_url = this.uploadedImage; // 更新 selectedStore
                            selectedStore.image_name = data.image_path; // 更新 selectedStore
                        } else {
                            document.getElementById('message').innerHTML = data.message;
                        }
                    })
                    .catch(error => {
                        document.getElementById('message').innerHTML = '上傳失敗：' + error.message;
                        console.error('Error:', error);
                    })
                    .finally(() => {
                        hideLoadingOverlay();
                    });
            }
        }">
            <div class="absolute inset-0 bg-black bg-opacity-50"></div>
            <div class="relative w-full h-full flex items-center justify-center p-4">
                <div class="p-6 relative bg-white w-full max-w-md rounded-lg shadow-lg"
                    @click.away="editArcadeModal = false">
                    <h2 class="text-lg font-semibold mb-4 text-gray-800">
                        {{ __('msg.edit') }}{{ __('msg.arcade') }}</h2>
                    <form id="uploadForm" enctype="multipart/form-data" @submit.prevent="uploadImage">
                        @csrf
                        <div class="mt-4">
                            <img :src="uploadedImage" alt="Store Image"
                                class="m-auto rounded-md dynamic-style h-full max-h-40">
                        </div>
                        <div id="notemsg" class="mt-4 text-red-500 text-end"></div>
                        <div class='flex space-x-1 items-center justify-between px-3'>
                            <div class="mb-4">
                                <label for="image" class="block text-sm font-medium text-gray-700">上傳圖片</label>
                                <input type="file" name="image" id="image"
                                    class="mt-1 block w-full text-sm text-gray-500" @change="previewImage">
                            </div>
                            <button type="submit"
                                class="max-h-8 bg-blue-500 hover:bg-blue-700 text-white font-bold p-1 rounded">
                                <x-icon.upload class='h-6 w-6 text-gray-500' />
                            </button>
                        </div>
                        <div id="message" class="mt-4 text-red-500"></div>
                    </form>
                    <form method="POST" :action="'/admin/arcades/' + selectedStore.id">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium">{{ __('msg.name') }}</label>
                            <input type="text" id="name" name="name" x-model="selectedStore.name"
                                class="w-full border px-2 py-1 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="address" class="block text-sm font-medium">{{ __('msg.address') }}</label>
                            <input type="text" id="address" name="address" x-model="selectedStore.address"
                                class="w-full border px-2 py-1 rounded-md">
                        </div>
                        <div class="mb-4">
                            <label for="phone" class="block text-sm font-medium">{{ __('msg.phone') }}</label>
                            <input type="text" id="phone" name="phone" x-model="selectedStore.phone"
                                class="w-full border px-2 py-1 rounded-md">
                        </div>

                        <div class="mb-4">
                            <label for="owner_id" class="block text-sm font-medium">店主</label>
                            <select name="owner_id" id="owner_id" x-model="selectedStore.owner_id"
                                class="w-full border px-2 py-1 rounded-md">
                                <option value="1">Admin</option>
                                @foreach ($arcadeOwners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->id }}. {{ $owner->name }}
                                    </option>
                                @endforeach

                                @foreach ($machieOwners as $owner)
                                    <option value="{{ $owner->id }}">{{ $owner->id }}. {{ $owner->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-row space-x-2">
                            <div class="mb-4 w-[50%]">
                                <label for="admin_arcade_currency"
                                    class="block text-sm font-medium text-gray-700">{{ __('msg.currency') }}</label>
                                <select id="admin_arcade_currency" name="currency" x-model="selectedStore.currency"
                                    required
                                    class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500">
                                    @foreach (config('bill_mappings', []) as $code => $displayNameKey)
                                        <option value="{{ $code }}">
                                            {{ __('msg.' . $code) }}
                                            ({{ __($code) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- 平台分成比例欄位 -->
                            <div class="mb-4 min-w-[40%]">
                                <label for="edit_share_pct"
                                    class="block text-sm font-medium text-gray-700">{{ __('msg.platform_share_pct') }}</label>
                                <div class="mt-1 flex items-center">
                                    <select name="share_pct" id="edit_share_pct" x-model="selectedStore.share_pct"
                                        class="w-full border px-2 py-1.5 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                        <option value="">{{ __('msg.default') }} / {{ __('msg.not_set') }}</option>
                                        {{-- 對應資料庫中的 NULL --}}
                                        @php
                                            $platformShareOptions = [0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0, 3.5, 5.0];
                                        @endphp
                                        @foreach ($platformShareOptions as $optionValue)
                                            <option value="{{ number_format($optionValue, 1) }}">
                                                {{ number_format($optionValue, 1) }}</option>
                                        @endforeach
                                    </select>
                                    <span class="ml-2 text-sm text-gray-700">%</span>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="image_url" x-model="selectedStore.image_name">
                        <div class="flex justify-end">
                            <button type="button"
                                class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4 mr-4"
                                @click="editArcadeModal = false">
                                取消
                            </button>
                            <button type="submit"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">
                                保存
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@php
    $title = __('msg.arcade_management');
@endphp
