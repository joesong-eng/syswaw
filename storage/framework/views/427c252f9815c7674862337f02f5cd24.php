<!-- resources/views/components/modal/arcade-edit.blade.php -->
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['arcadeOwners']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['arcadeOwners']); ?>
<?php foreach (array_filter((['arcadeOwners']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if($errors->any()): ?>
    <div class="mb-4 text-red-600">
        <ul>
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </ul>
    </div>
<?php endif; ?>

<div x-cloak x-show="editArcadeModal" class="fixed inset-0 z-50" 
     x-data="{
        uploadedImage: '',
        imageUrl: '',
        init() {
            this.$watch('selectedStore', value => {
                this.uploadedImage = value.image_url ? value.image_url : '<?php echo e(Storage::url('images/default-store.jpg')); ?>';
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
                this.uploadedImage = '<?php echo e(Storage::url('images/default-store.jpg')); ?>';
            }
        },
        uploadImage() {
            let form = document.getElementById('uploadForm');
            let formData = new FormData(form);
            fetch('<?php echo e(route('upload.image')); ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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
                    this.uploadedImage = '<?php echo e(Storage::url('images/')); ?>' + data.image_path;
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
        <h2 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-100"><?php echo e(__('msg.edit')); ?><?php echo e(__('msg.arcade')); ?></h2>
        
            <!--selectedStore.image_url: /storage/images/1740806669.jpg -->
            <form id="uploadForm" enctype="multipart/form-data" @submit.prevent="uploadImage">
                <?php echo csrf_field(); ?>
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
                        <?php if (isset($component)) { $__componentOriginal2357aa3918a86a7d791191bf6ff66a03 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2357aa3918a86a7d791191bf6ff66a03 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon.upload','data' => ['class' => 'h-6 w-6 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icon.upload'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2357aa3918a86a7d791191bf6ff66a03)): ?>
<?php $attributes = $__attributesOriginal2357aa3918a86a7d791191bf6ff66a03; ?>
<?php unset($__attributesOriginal2357aa3918a86a7d791191bf6ff66a03); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2357aa3918a86a7d791191bf6ff66a03)): ?>
<?php $component = $__componentOriginal2357aa3918a86a7d791191bf6ff66a03; ?>
<?php unset($__componentOriginal2357aa3918a86a7d791191bf6ff66a03); ?>
<?php endif; ?>
                    </button>
                </div>
                <div id="message" class="mt-4 text-red-500"></div>
            </form>

            <form :action="`/arcades/update/${selectedStore.id}`" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium dark:text-gray-300"><?php echo e(__('msg.name')); ?></label>
                    <input type="text" id="name" name="name" x-model="selectedStore.name" class="w-full border px-2 py-1 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-medium dark:text-gray-300"><?php echo e(__('msg.address')); ?></label>
                    <input type="text" id="address" name="address" x-model="selectedStore.address" class="w-full border px-2 py-1 rounded-md">
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-sm font-medium dark:text-gray-300"><?php echo e(__('msg.phone')); ?></label>
                    <input type="text" id="phone" name="phone" x-model="selectedStore.phone" class="w-full border px-2 py-1 rounded-md">
                </div>
                <!-- 如果登入帳戶是admin，則顯示所有店主名稱 -->
                <?php if(Auth::user()->hasRole('admin')): ?>
                    <div class="mb-4">
                        <label for="owner_id" class="block text-sm font-medium">店主</label>
                        <select name="owner_id" id="owner_id" x-model="selectedStore.owner_id"
                            class="w-full border px-2 py-1 rounded-md">
                            <option value="1">Admin</option>
                            <?php $__currentLoopData = $arcadeOwners; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $owner): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($owner->id); ?>"><?php echo e($owner->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="owner_id" x-model="selectedStore.owner_id" readonly>
                <?php endif; ?>
                <input type="hidden" name="image_url" x-model="selectedStore.image_name">

                
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
</div><?php /**PATH /www/wwwroot/syswaw/resources/views/components/modal/arcade-edit.blade.php ENDPATH**/ ?>