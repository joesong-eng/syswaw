<div id="sidebar" 
    class="sidebar ease-in-out transition-transform duration-300
        bg-gray-100 dark:bg-gray-800 shadow-lg overflow-auto w-full
        fixed top-12 left-0 z-10 
        sm:pt-14 sm:w-64 md:w-72 
        h-12 sm:h-full sm:static sm:block
        max-h-[calc(100vh-10px)]
        px-2 mx-1" >
    <!-- 移動端：固定高度橫向滑動；桌面端：垂直佈局 -->
    <div id="sidebarkid" 
         class="sidebarkid flex sm:flex-col h-12 sm:h-full overflow-x-auto sm:overflow-y-auto max-h-[calc(100vh-48px)] sm:max-h-[calc(100vh-56px)] whitespace-nowrap sm:whitespace-normal">
        
        <!-- Admin 專屬功能 -->
        <?php if(Auth::user()->hasRole('admin')): ?>
            <div class="hidden sm:block text-center p-2 rounded-lg shadow hover:bg-gray-50 dark:hover:bg-gray-600">
                <div class="text-lg font-thin text-gray-900 dark:text-gray-100 break-words whitespace-normal max-w-48 m-auto">
                    <?php echo e(__('msg.welcome', ['name' => Auth::user()->name])); ?>

                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 break-words whitespace-normal max-w-48 m-auto">
                    <?php echo e(__('msg.profile_info')); ?>

                </div>
            </div>
            <hr class="hidden sm:block">
            <!-- 導航項 -->
            <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'admin.arcadeKey','icon' => 'key','title' => ''.e(__('msg.arcade_key_management')).'','description' => ''.e(__('msg.arcade_key_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.arcadeKey','icon' => 'key','title' => ''.e(__('msg.arcade_key_management')).'','description' => ''.e(__('msg.arcade_key_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'admin.arcades','icon' => 'stores','title' => ''.e(__('msg.arcade_management')).'','description' => ''.e(__('msg.arcade_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.arcades','icon' => 'stores','title' => ''.e(__('msg.arcade_management')).'','description' => ''.e(__('msg.arcade_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'chips.index','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'chips.index','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'admin.machines','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'admin.machines','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']); ?>
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
        <?php endif; ?>

        <!-- arcade-owner -->
        <?php if(Auth::user()->hasRole('arcade-owner')): ?>
            <?php if(Auth::user()->arcade): ?>
                <div class="hidden sm:block shadow-lg p-2">
                    <a href="<?php echo e(route('arcades.index')); ?>" class="text-sm text-center hover:text-blue-600">
                        <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2"><?php echo e(__('msg.arcade_info')); ?></h3>
                        <div class="space-y-2 overflow-auto shadow-lg">
                            <div class="flex items-center justify-start">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.name')); ?></span>
                                <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->name); ?></p>
                            </div>
                            <div class="flex items-center justify-start">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.address')); ?></span>
                                <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->address); ?></p>
                            </div>
                            <div class="flex items-center justify-start">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.phone')); ?></span>
                                <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->phone); ?></p>
                            </div>
                            <div class="flex items-center justify-start pb-2">
                                <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.owner')); ?></span>
                                <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->owner->name); ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endif; ?>
            <hr class="hidden sm:block">
            <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.ProfileInfo')).'','description' => ''.e(__('msg.personal_info_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.ProfileInfo')).'','description' => ''.e(__('msg.personal_info_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'staff','icon' => 'manager','title' => ''.e(__('msg.staff_management')).'','description' => ''.e(__('msg.staff_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'staff','icon' => 'manager','title' => ''.e(__('msg.staff_management')).'','description' => ''.e(__('msg.staff_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'chips.index','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'chips.index','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.index','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.index','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.index','icon' => 'manager','title' => ''.e(__('msg.add_machine_owner')).'','description' => ''.e(__('msg.add_machine_owner_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.index','icon' => 'manager','title' => ''.e(__('msg.add_machine_owner')).'','description' => ''.e(__('msg.add_machine_owner_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']); ?>
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
        <?php endif; ?>

        <!-- arcade-staff -->
        <?php if(Auth::user()->hasRole('arcade-staff')): ?>
            <?php if((Auth::user()->parent)->arcade): ?>
            <div class="hidden sm:block shadow-lg p-2">
                <a href="<?php echo e(route('arcades.index')); ?>" class="text-sm text-center hover:text-blue-600">
                    <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2"><?php echo e(__('msg.arcade_info')); ?></h3>
                    <div class="space-y-2 overflow-auto shadow-lg">
                        <div class="flex items-center justify-start">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.arcade')); ?></span>
                            <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->name); ?></p>
                        </div>
                        <div class="flex items-center justify-start">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.address')); ?></span>
                            <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->address); ?></p>
                        </div>
                        <div class="flex items-center justify-start">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.phone')); ?></span>
                            <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->phone); ?></p>
                        </div>
                        <div class="flex items-center justify-start pb-2">
                            <span class="font-bold text-sm text-gray-600 dark:text-gray-300 px-2"><?php echo e(__('msg.owner')); ?></span>
                            <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->parent->arcade->owner->name); ?></p>
                        </div>
                    </div>
                </a>
            </div>
            <?php endif; ?>
            <hr class="hidden sm:block">
            <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.personal_info')).'','description' => ''.e(__('msg.personal_info_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.personal_info')).'','description' => ''.e(__('msg.personal_info_desc')).'']); ?>
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
        
            <?php if(in_array('chips.index', Auth::user()->sidebar_permissions ?? [])): ?>
                <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'chips.index','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'chips.index','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']); ?>
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
            <?php endif; ?>
        
            <?php if(in_array('machine.index', Auth::user()->sidebar_permissions ?? [])): ?>
                <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.index','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.index','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']); ?>
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
            <?php endif; ?>
        
            <?php if(in_array('add_machine_owner', Auth::user()->sidebar_permissions ?? [])): ?>
                <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.index','icon' => 'manager','title' => ''.e(__('msg.add_machine_owner')).'','description' => ''.e(__('msg.add_machine_owner_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.index','icon' => 'manager','title' => ''.e(__('msg.add_machine_owner')).'','description' => ''.e(__('msg.add_machine_owner_desc')).'']); ?>
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
            <?php endif; ?>
        
            <?php if(in_array('transactions.index', Auth::user()->sidebar_permissions ?? [])): ?>
                <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']); ?>
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
            <?php endif; ?>
        <?php endif; ?>

        <!-- machine-owner -->
        <?php if(Auth::user()->hasRole('machine-owner')): ?>
            <div class="hidden sm:block bg-white dark:bg-gray-800 p-4 shadow-lg flex-shrink-0 w-48 sm:w-auto">
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2"><?php echo e(__('msg.machine_info')); ?></h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-gray-600 dark:text-gray-300"><?php echo e(__('msg.machine_owner')); ?>:</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100"><?php echo e(Auth::user()->name); ?></p>
                    </div>
                </div>
            </div>
            <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.ProfileInfo')).'','description' => ''.e(__('msg.personal_info_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.ProfileInfo')).'','description' => ''.e(__('msg.personal_info_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.manager','icon' => 'manager','title' => ''.e(__('msg.staff_management')).'','description' => ''.e(__('msg.staff_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.manager','icon' => 'manager','title' => ''.e(__('msg.staff_management')).'','description' => ''.e(__('msg.staff_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.machine_key','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.machine_key','icon' => 'machine-key','title' => ''.e(__('msg.chip_token')).'','description' => ''.e(__('msg.machine_key_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.machines','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.machines','icon' => 'machines','title' => ''.e(__('msg.machine_management')).'','description' => ''.e(__('msg.machine_management_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.visualStore','icon' => 'stores','title' => ''.e(__('msg.vsstore')).'','description' => ''.e(__('msg.vsarcade_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.visualStore','icon' => 'stores','title' => ''.e(__('msg.vsstore')).'','description' => ''.e(__('msg.vsarcade_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']); ?>
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
        <?php endif; ?>

        <!-- machine-manager -->
        <?php if(Auth::user()->hasRole('machine-manager')): ?>
            <div class="hidden sm:block bg-white dark:bg-gray-800 p-4 shadow-lg">
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-2"><?php echo e(__('msg.machine_info')); ?></h3>
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="font-bold text-sm text-gray-600 dark:text-gray-300"><?php echo e(__('msg.machine_owner')); ?>:</span>
                        <p class="text-sm text-gray-900 dark:text-gray-100">
                            <?php echo e(Auth::user()->parent ? Auth::user()->parent->name : '無店主資料'); ?>

                        </p>
                    </div>
                </div>
            </div>
            <?php if (isset($component)) { $__componentOriginal5add08b1ca1dea14137f68eece323077 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5add08b1ca1dea14137f68eece323077 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.machines','icon' => 'machines','title' => ''.e(__('msg.machine')).'','description' => ''.e(__('msg.machine.dashboard')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.machines','icon' => 'machines','title' => ''.e(__('msg.machine')).'','description' => ''.e(__('msg.machine.dashboard')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.ProfileInfo')).'','description' => ''.e(__('msg.ProfileDiscription')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'profile.edit','icon' => 'user','title' => ''.e(__('msg.ProfileInfo')).'','description' => ''.e(__('msg.ProfileDiscription')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'machine.visualStore','icon' => 'store','title' => ''.e(__('msg.vsstore')).'','description' => ''.e(__('msg.vsarcade_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'machine.visualStore','icon' => 'store','title' => ''.e(__('msg.vsstore')).'','description' => ''.e(__('msg.vsarcade_desc')).'']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.sidebar-link','data' => ['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.sidebar-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['route' => 'transactions.index','icon' => 'transactions','title' => ''.e(__('msg.transaction_management')).'','description' => ''.e(__('msg.transaction_management_desc')).'']); ?>
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
        <?php endif; ?>
    </div>
</div>

<?php /**PATH /www/wwwroot/syswaw/resources/views/components/modal/sidebar.blade.php ENDPATH**/ ?>