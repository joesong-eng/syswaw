<?php $__env->startSection('content'); ?>
<?php if(session('success')): ?>
    <div id="success-message" class="absolute message ms-4 p-2 text-green-800 bg-green-100 z-10 border border-green-200 rounded-lg  duration-1000 ease-out transform transition-transform slide-in"><?php echo e(session('success')); ?></div>
<?php endif; ?>
<?php if($errors->any()): ?>
    <div id="error-message" class="mb-4 text-red-600">
    <ul>
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <li><?php echo e($error); ?></li>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<?php endif; ?>
<div class="flex justify-center bg-gray-100 dark:bg-gray-900" 
    x-data="{ createRoleModal: false, editModal: false, selectedRole: {} }">
    <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg max-w-md">
        <div class="flex justify-between items-center mb-2 px-6 pt-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                <?php echo e(__('msg.role_management')); ?>

            </h2>
            <button @click="createRoleModal = true" 
            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                <?php echo e(__('msg.create')); ?><?php echo e(__('msg.role')); ?>

            </button>
        </div>

        <div class="container mx-auto px-2 pb-3">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg px-2 ">
                <!-- Header Row -->
                <div class="flex items-center text-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100  shadow-md">
                    <div class="w-[10%]">ID</div>
                    <div class="w-[40%] border-l border-gray-200 dark:border-gray-700 "><?php echo e(__('msg.name')); ?></div>
                    <div class="w-[20%] border-l border-gray-200 dark:border-gray-700">Level</div>
                    <div class="w-[30%] border-l border-gray-200 dark:border-gray-700 "><?php echo e(__('msg.actions')); ?></div>
                </div>
        
                <!-- Data Rows -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                    <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 py-1">
                            <div class="w-[10%] break-words text-center "><?php echo e($role->id); ?></div>
                            <div class="w-[40%] break-words border-l px-2"><?php echo e($role->name); ?></div>
                            <div class="w-[20%] break-words border-l px-2 text-center"><?php echo e($role->level); ?></div>
                            <div class="w-[30%] break-words hidden"><?php echo e($role->guard_name); ?></div>
                            <div class=" w-[30%] items-center text-center m-auto border-1 border-l space-x-1">
                                <button class="inline-block px-1 py-1 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition" 
                                @click="editModal = true; selectedRole = { id: <?php echo e($role->id); ?>, name: '<?php echo e($role->name); ?>',level: '<?php echo e($role->level); ?>', slug: '<?php echo e($role->slug); ?>' }">
                                    <?php if (isset($component)) { $__componentOriginal472c9e5ad134025081e5e0d35f1abca4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4 = $attributes; } ?>
<?php $component = App\View\Components\SvgIcons::resolve(['name' => 'edit','classes' => 'h-6 w-6'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                                </button>
                                <form action="<?php echo e(route('admin.roles.destroy', $role)); ?>" method="POST" class="inline-block">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" 
                                    class="px-1 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition" 
                                    onclick="return confirm('<?php echo e(__('msg.confirm_delete')); ?>');">
                                        <?php if (isset($component)) { $__componentOriginal472c9e5ad134025081e5e0d35f1abca4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal472c9e5ad134025081e5e0d35f1abca4 = $attributes; } ?>
<?php $component = App\View\Components\SvgIcons::resolve(['name' => 'delete','classes' => 'h-6 w-6'] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
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
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

            </div>
        </div>

    </div>
    <!-- Modals -->
    <?php if (isset($component)) { $__componentOriginal0927c9da12f675008286e04491d033c7 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0927c9da12f675008286e04491d033c7 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.role_create-modal','data' => ['xShow' => 'createRoleModal','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.role_create-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'createRoleModal','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0927c9da12f675008286e04491d033c7)): ?>
<?php $attributes = $__attributesOriginal0927c9da12f675008286e04491d033c7; ?>
<?php unset($__attributesOriginal0927c9da12f675008286e04491d033c7); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0927c9da12f675008286e04491d033c7)): ?>
<?php $component = $__componentOriginal0927c9da12f675008286e04491d033c7; ?>
<?php unset($__componentOriginal0927c9da12f675008286e04491d033c7); ?>
<?php endif; ?>
    <?php if (isset($component)) { $__componentOriginald5fc985f4ca6aaddf82aa80794be20d5 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald5fc985f4ca6aaddf82aa80794be20d5 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.role_edit-modal','data' => ['xShow' => 'editModal','xCloak' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.role_edit-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-show' => 'editModal','x-cloak' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginald5fc985f4ca6aaddf82aa80794be20d5)): ?>
<?php $attributes = $__attributesOriginald5fc985f4ca6aaddf82aa80794be20d5; ?>
<?php unset($__attributesOriginald5fc985f4ca6aaddf82aa80794be20d5); ?>
<?php endif; ?>
<?php if (isset($__componentOriginald5fc985f4ca6aaddf82aa80794be20d5)): ?>
<?php $component = $__componentOriginald5fc985f4ca6aaddf82aa80794be20d5; ?>
<?php unset($__componentOriginald5fc985f4ca6aaddf82aa80794be20d5); ?>
<?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php
    $title = __('msg.role_management');
?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/roles/index.blade.php ENDPATH**/ ?>