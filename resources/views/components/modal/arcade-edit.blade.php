<!-- resources/views/components/modal/arcade-edit.blade.php -->
@props(['arcadeOwners'])
@if ($errors->any())
    <div class="mb-4 text-red-600">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div x-cloak x-show="editArcadeModal" class="fixed inset-0 z-50" 
     x-data="{
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
            fetch('{{ route('upload.image') }}', {
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
        <div class="p-6 relative bg-white dark:bg-gray-800 w-full max-w-md rounded-lg shadow-lg" 
        @click.away="editArcadeModal = false">
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100">{{__('msg.edit')}}{{__('msg.arcade')}}</h2>
        {{-- <div>Debug image_url: <span x-text="selectedStore.image_url"></span></div>
        <div>Debug uploadedImage: <span x-text="uploadedImage"></span></div> --}}
            <!--selectedStore.image_url: /storage/images/1740806669.jpg -->
            <form id="uploadForm" enctype="multipart/form-data" @submit.prevent="uploadImage">
                @csrf
                <div class="mt-4">
                    <img :src="uploadedImage" alt="Store Image"
                    class="m-auto rounded-md dynamic-style h-full max-h-40">
                </div>
                <div id="notemsg" class="mt-4 text-red-500 text-end"></div>
                <div class='flex space-x-1 items-center justify-between px-3'>
                    <div class="mb-4">
                        <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">上傳圖片</label>
                        <input type="file" name="image" id="image" class="mt-1 block w-full text-sm text-gray-500" @change="previewImage">
                    </div>
                    <button type="submit" class="max-h-8 bg-blue-500 hover:bg-blue-700 text-white font-bold p-1 rounded">
                        <x-icon.upload class='h-6 w-6 text-gray-500' />
                    </button>
                </div>
                <div id="message" class="mt-4 text-red-500"></div>
            </form>

            <form :action="`/arcades/update/${selectedStore.id}`" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium dark:text-gray-300">{{__('msg.name')}}</label>
                    <input type="text" id="name" name="name" x-model="selectedStore.name" class="w-full border px-2 py-1 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium dark:text-gray-300">{{__('msg.address')}}</label>
                    <input type="text" id="address" name="address" x-model="selectedStore.address" class="w-full border px-2 py-1 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium dark:text-gray-300">{{__('msg.phone')}}</label>
                    <input type="text" id="phone" name="phone" x-model="selectedStore.phone" class="w-full border px-2 py-1 rounded-md">
                </div>
                <!-- 如果登入帳戶是admin，則顯示所有店主名稱 -->
                @if(Auth::user()->hasRole('admin'))
                    <div class="mb-4">
                        <label for="owner_id" class="block text-sm font-medium">店主</label>
                        <select name="owner_id" id="owner_id" x-model="selectedStore.owner_id"
                            class="w-full border px-2 py-1 rounded-md">
                            <option value="1">Admin</option>
                            @foreach ($arcadeOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <input type="hidden" name="owner_id" x-model="selectedStore.owner_id" readonly>
                @endif
                <input type="hidden" name="image_url" x-model="selectedStore.image_name">

                {{-- 把按鈕放到畫面右邊 --}}
                <div class="flex justify-end">
                    <button type="button" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded mt-4 mr-4"
                        @click="editArcadeModal = false">
                        取消
                    </button>
                    <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mt-4">
                        保存
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>