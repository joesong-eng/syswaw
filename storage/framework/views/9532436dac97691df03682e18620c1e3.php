<?php $__env->startSection('content'); ?>
    <div class="py-1">
        <div class=" max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="relative bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- User Stats -->
                
                    <div class="bg-white dark:bg-gray-700 overflow-hidden shadow rounded-lg">
                        <div class="text-right">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                註冊時間: <?php echo e(Auth::user()->created_at->format('Y-m-d')); ?>

                            </dt>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">
                                帳戶狀態:<?php echo e(Auth::user()->is_active ? '活躍' : '停用'); ?>

                            </dt>
                        </div>
                    </div>
                
                <div class="flex gap-1">
                    <!-- Stats Card 1 -->
                    <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-3">
                        <div class="text-center">
                            <h2 class="text-gray-600 dark:text-gray-300 text-sm font-medium">總用戶數</h2>
                            <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100"><?php echo e(\App\Models\User::count()); ?></p>
                        </div>
                    </div>
                
                    <!-- Stats Card 2 -->
                    
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 dark:text-gray-300 text-sm font-medium">活躍用戶</h2>
                                <p class="text-2xl text-center font-semibold text-gray-800 dark:text-gray-100"><?php echo e(\App\Models\User::where('is_active', true)->count()); ?></p>
                            </div>
                        </div>
                    
                
                    <!-- Stats Card 3 -->
                    
                        <div class="bg-white dark:bg-gray-700 rounded-lg shadow p-3">
                            <div class="text-center">
                                <h2 class="text-gray-600 dark:text-gray-300 text-sm font-medium">角色總數</h2>
                                <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100"><?php echo e(\App\Models\Role::count()); ?></p>
                            </div>
                        </div>
                    
                </div>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">快速操作</h3>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'admin.users','icon' => 'user','title' => ''.e(__('msg.user_management')).'','description' => ''.e(__('msg.user_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.users','icon' => 'user','title' => ''.e(__('msg.user_management')).'','description' => ''.e(__('msg.user_management_desc')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5add08b1ca1dea14137f68eece323077)): ?>
<?php $attributes = $__attributesOriginal5add08b1ca1dea14137f68eece323077; ?>
<?php unset($__attributesOriginal5add08b1ca1dea14137f68eece323077); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5add08b1ca1dea14137f68eece323077)): ?>
<?php $component = $__componentOriginal5add08b1ca1dea14137f68eece323077; ?>
<?php unset($__componentOriginal5add08b1ca1dea14137f68eece323077); ?>
<?php endif; ?>
                    
                        <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'roles.index','icon' => 'role','title' => ''.e(__('msg.role_management')).'','description' => ''.e(__('msg.role_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'roles.index','icon' => 'role','title' => ''.e(__('msg.role_management')).'','description' => ''.e(__('msg.role_management_desc')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5add08b1ca1dea14137f68eece323077)): ?>
<?php $attributes = $__attributesOriginal5add08b1ca1dea14137f68eece323077; ?>
<?php unset($__attributesOriginal5add08b1ca1dea14137f68eece323077); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5add08b1ca1dea14137f68eece323077)): ?>
<?php $component = $__componentOriginal5add08b1ca1dea14137f68eece323077; ?>
<?php unset($__componentOriginal5add08b1ca1dea14137f68eece323077); ?>
<?php endif; ?>
                        

                        <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'admin.tcp-server','icon' => 'transactions','title' => ''.e(__('msg.data_stream')).'','description' => ''.e(__('msg.data_stream_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.tcp-server','icon' => 'transactions','title' => ''.e(__('msg.data_stream')).'','description' => ''.e(__('msg.data_stream_desc')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5add08b1ca1dea14137f68eece323077)): ?>
<?php $attributes = $__attributesOriginal5add08b1ca1dea14137f68eece323077; ?>
<?php unset($__attributesOriginal5add08b1ca1dea14137f68eece323077); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5add08b1ca1dea14137f68eece323077)): ?>
<?php $component = $__componentOriginal5add08b1ca1dea14137f68eece323077; ?>
<?php unset($__componentOriginal5add08b1ca1dea14137f68eece323077); ?>
<?php endif; ?>
                    
                        
                    </div>
                </div>
            </div>
        </div>

    </div>
<?php $__env->stopSection(); ?>
<?php
    $title = 'Dashboard.admin';
?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/admin/dashboard.blade.php ENDPATH**/ ?>