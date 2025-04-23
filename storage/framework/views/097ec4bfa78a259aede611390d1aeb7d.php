<?php $__env->startSection('content'); ?>
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900" 
        x-data="{ editArcadeModal: false, selectedStore: {}, addStoreModal: false,arcadeOwners:  <?php echo e($arcadeOwners); ?>  }">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    <?php echo e(__('msg.arcade_management')); ?>

                </h2>
                <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
                    <a href="/admin/arcade/create"><?php echo e(__('msg.create_arcade')); ?></a>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
            </div>
            <!-- 商店列表 -->
            <div class="container mx-auto px-2 pb-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-start border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg">
                        <div class="w-[19%] items-center text-center"> <?php echo e(__('msg.name')); ?> </div>
                        <div class="w-[30%] items-center text-center"> <?php echo e(__('msg.address')); ?> </div>
                        <div class="w-[17%] items-center text-center"> <?php echo e(__('msg.owner')); ?> </div>
                        <div class="w-[10%] items-center text-center"> <?php echo e(__('msg.type')); ?> </div>
                        <div class="w-[9%] items-center text-center">  <?php echo e(__('msg.status')); ?> </div>
                        <div class="w-[15%] items-center text-center"> <?php echo e(__('msg.active')); ?> </div>
                    </div>

                    <!-- Data Rows -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)] py-2 ">
                        <?php $__currentLoopData = $arcades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arcade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100 py-1">
                                <div class="w-[19%] break-words text-center"><?php echo e($arcade->name); ?></div>
                                <div class="w-[30%] break-words text-center"><?php echo e($arcade->address); ?></div>
                                <div class="w-[17%] break-words text-center"><?php echo e($arcade->owner->name ?? 'N/A'); ?></div>
                                <div class="w-[10%] break-words text-center"><?php echo e($arcade->type ?? 'N/A'); ?></div>
                                <div class="w-[9%]  justify-items-center text-center m-auto space-x-1 border-l">
                                    <form action="<?php echo e(route('admin.arcade.toggleActive', $arcade->id)); ?>" method="POST" 
                                        class="flex justify-items-center space-x-1"
                                        onsubmit="return confirm('Are you sure you want to <?php echo e($arcade->is_active ? 'deactivate' : 'activate'); ?> this arcade?');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input type="hidden" name="is_active" value="<?php echo e($arcade->is_active ? 0 : 1); ?>">
                                        <button type="submit" 
                                            class="m-auto text-white transition p-1">
                                            <?php if($arcade->is_active): ?>
                                                <?php if (isset($component)) { $__componentOriginal472c9e5ad134025081e5e0d35f1abca4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4 = $attributes; } ?>
<?php $component = App\View\Components\SvgIcons::resolve(['name' => 'statusT','classes' => 'h-6 w-6'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('svg-icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\SvgIcons::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $attributes = $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $component = $__componentOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
                                            <?php else: ?>   
                                                <?php if (isset($component)) { $__componentOriginal472c9e5ad134025081e5e0d35f1abca4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4 = $attributes; } ?>
<?php $component = App\View\Components\SvgIcons::resolve(['name' => 'statusF','classes' => 'h-6 w-6'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('svg-icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\SvgIcons::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $attributes = $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $component = $__componentOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
                                            <?php endif; ?>
                                        </button>
                                    </form></div>
                                <div class="w-[15%] break-words justify-items-center text-center m-auto space-x-1">
                                    <!-- 編輯按鈕 -->
                                    <div class="flex justify-items-between space-x-1">
                                        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto','@click' => 'editArcadeModal = true; selectedStore = { 
                                                id: '.e($arcade->id).', 
                                                name: \''.e($arcade->name).'\', 
                                                image_url: \''.e($arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg')).'\', 
                                                image_name: \''.e($arcade->image_url ? $arcade->image_url :('default-store.jpg')).'\', 
                                                address: \''.e($arcade->address).'\', 
                                                phone: \''.e($arcade->phone).'\', 
                                                owner_name: \''.e($arcade->owner->name).'\', 
                                                owner_id: \''.e($arcade->owner_id).'\', 
                                                business_hours: \''.e($arcade->business_hours).'\', 
                                                revenue_split: '.e($arcade->revenue_split).'

                                            }']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto','@click' => 'editArcadeModal = true; selectedStore = { 
                                                id: '.e($arcade->id).', 
                                                name: \''.e($arcade->name).'\', 
                                                image_url: \''.e($arcade->image_url ? Storage::url('images/' . $arcade->image_url) : Storage::url('images/default-store.jpg')).'\', 
                                                image_name: \''.e($arcade->image_url ? $arcade->image_url :('default-store.jpg')).'\', 
                                                address: \''.e($arcade->address).'\', 
                                                phone: \''.e($arcade->phone).'\', 
                                                owner_name: \''.e($arcade->owner->name).'\', 
                                                owner_id: \''.e($arcade->owner_id).'\', 
                                                business_hours: \''.e($arcade->business_hours).'\', 
                                                revenue_split: '.e($arcade->revenue_split).'

                                            }']); ?>
                                            <?php if (isset($component)) { $__componentOriginal472c9e5ad134025081e5e0d35f1abca4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4 = $attributes; } ?>
<?php $component = App\View\Components\SvgIcons::resolve(['name' => 'edit','classes' => 'h-4 w-4'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('svg-icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\SvgIcons::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $attributes = $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $component = $__componentOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                                        <form action="<?php echo e(route('arcade.destroy', $arcade->id)); ?>" method="POST" class="m-0">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto','type' => 'submit','onclick' => 'return confirm(\''.e(__('msg.confirm_active')).' '.e(__('msg.delete')).''.e(__('msg.this_machine')).''.e(__('msg.zh_ask')).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto','type' => 'submit','onclick' => 'return confirm(\''.e(__('msg.confirm_active')).' '.e(__('msg.delete')).''.e(__('msg.this_machine')).''.e(__('msg.zh_ask')).'\')']); ?>
                                                <?php if (isset($component)) { $__componentOriginal472c9e5ad134025081e5e0d35f1abca4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4 = $attributes; } ?>
<?php $component = App\View\Components\SvgIcons::resolve(['name' => 'delete','classes' => 'h-4 w-4'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('svg-icons'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\SvgIcons::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $attributes = $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__attributesOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4)): ?>
<?php $component = $__componentOriginal472c9e5ad134025081e5e0d35f1abca4; ?>
<?php unset($__componentOriginal472c9e5ad134025081e5e0d35f1abca4); ?>
<?php endif; ?>
                                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $attributes = $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561)): ?>
<?php $component = $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561; ?>
<?php unset($__componentOriginald0f1fd2689e4bb7060122a5b91fe8561); ?>
<?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($component)) { $__componentOriginal72bc7283072d427f5f6719b95cec8c0f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal72bc7283072d427f5f6719b95cec8c0f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.arcade-edit','data' => ['arcadeOwners' => $arcadeOwners]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.arcade-edit'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['arcadeOwners' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($arcadeOwners)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal72bc7283072d427f5f6719b95cec8c0f)): ?>
<?php $attributes = $__attributesOriginal72bc7283072d427f5f6719b95cec8c0f; ?>
<?php unset($__attributesOriginal72bc7283072d427f5f6719b95cec8c0f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal72bc7283072d427f5f6719b95cec8c0f)): ?>
<?php $component = $__componentOriginal72bc7283072d427f5f6719b95cec8c0f; ?>
<?php unset($__componentOriginal72bc7283072d427f5f6719b95cec8c0f); ?>
<?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>
<?php
    $title = __('msg.arcade_management');
?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/admin/arcade/index.blade.php ENDPATH**/ ?>