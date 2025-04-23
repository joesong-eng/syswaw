<?php $__env->startSection('content'); ?>
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900" 
        x-data="{ createUserModal: false, editUserModal: false, selectedUser: {},
            confirmDeactivate(userId, isActive) {if (confirm('Are you sure you want to deactivate this user?')) {
                console.log('Deactivating user:', userId);}}}">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4"><?php echo e(__('msg.user_management')); ?></h2>
                <button @click="createUserModal = true; $nextTick(() => checkRole('create'))" 
                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                        <?php echo e(__('msg.create')); ?><?php echo e(__('msg.user')); ?>

                    </button>
            </div>
            <div class="container mx-auto px-2 pb-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg  text-center">
                        <div class="w-[4%] ">#</div>
                        <div class="w-[16%] text-center px-1"><?php echo e(__('msg.name')); ?></div>
                        <div class="w-[40%] text-center"><?php echo e(__('msg.verify')); ?>/<?php echo e(__('msg.email')); ?></div>
                        <div class="w-[16%] text-center min-h-full border-r-2 "><?php echo e(__('msg.role')); ?></div>
                        <div class="w-[9%] text-center"><?php echo e(__('msg.status')); ?></div>
                        <div class="w-[15%] flex items-center justify-center "><?php echo e(__('msg.actions')); ?></div>
                    </div>
                    <!-- Data Rows -->
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-start border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100 py-1">
                            <div class="w-[4%]  px-1 m-auto border-r text-xs "><?php echo e($user->id); ?></div>
                            <div class="w-[16%] px-1 break-words m-auto text-center">
                                <div class="font-thin w-full text-end pe-1" style="font-size: xx-small">
                                    <div class="font-thin" style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo e(optional($user->parent)->name); ?></div>
                                </div>
                                <?php echo e($user->name); ?>

                            </div>
                            <div class="flex w-[40%] px-1 break-words m-auto items-center justify-center" style="overflow-wrap: anywhere;">
                                <?php if(!$user->email_verified_at): ?>
                                    <form action="<?php echo e(route('admin.users.verify', $user->id)); ?>" method="POST">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600 transition">
                                            <?php echo e(__('msg.verify')); ?>

                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs px-2 py-1 text-end rounded transition">
                                        <?php if (isset($component)) { $__componentOriginal60bd8e97e81cdd62dddb783b1509c0b6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal60bd8e97e81cdd62dddb783b1509c0b6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon.verified','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icon.verified'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal60bd8e97e81cdd62dddb783b1509c0b6)): ?>
<?php $attributes = $__attributesOriginal60bd8e97e81cdd62dddb783b1509c0b6; ?>
<?php unset($__attributesOriginal60bd8e97e81cdd62dddb783b1509c0b6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal60bd8e97e81cdd62dddb783b1509c0b6)): ?>
<?php $component = $__componentOriginal60bd8e97e81cdd62dddb783b1509c0b6; ?>
<?php unset($__componentOriginal60bd8e97e81cdd62dddb783b1509c0b6); ?>
<?php endif; ?>
                                    </span>
                                <?php endif; ?>
                                <span class="ps-2">
                                    <?php echo e($user->email); ?>

                                </span>
                            </div>
                            <div class="w-[16%] min-h-full border-x px-1"><?php echo e($user->roles->pluck('name')->join(', ')); ?></div>
                            <div class="flex w-[9%] justify-center items-center m-auto">
                                <form action="<?php echo e(route('users.deactivate', $user)); ?>" method="POST" 
                                    onsubmit="return confirm('Are you sure you want to <?php echo e($user->is_active ? 'deactivate' : 'activate'); ?> this user?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="is_active" value="<?php echo e($user->is_active ? 0 : 1); ?>">
                                    <button type="submit" class="inline-block">
                                        <?php if($user->is_active): ?>
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
                            <div class="flex w-[15%] break-words justify-center items-center  m-auto space-x-1 ">
                                
                                    <!-- 編輯按鈕 -->
                                    <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto','@click' => 'editUserModal = true; selectedUser = { 
                                            id: '.e($user->id).', 
                                            name: \''.e($user->name).'\', 
                                            email: \''.e($user->email).'\', 
                                            role: \''.e(optional($user->roles->first())->name).'\',
                                            parent_name: \''.e(optional($user->parent)->name).'\'
                                        }; $nextTick(() => checkRole(\'edit\'))']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-blue-500 text-white rounded-md hover:bg-blue-600 transition hover:text-blue-700 !p-1 !m-0 !my-auto','@click' => 'editUserModal = true; selectedUser = { 
                                            id: '.e($user->id).', 
                                            name: \''.e($user->name).'\', 
                                            email: \''.e($user->email).'\', 
                                            role: \''.e(optional($user->roles->first())->name).'\',
                                            parent_name: \''.e(optional($user->parent)->name).'\'
                                        }; $nextTick(() => checkRole(\'edit\'))']); ?>
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
                                    
                                    <!-- 刪除按鈕 -->
                                    <form action="<?php echo e(route('admin.user.destroy', $user->id)); ?>" method="POST" class="m-0">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['class' => 'bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto','type' => 'submit','onclick' => 'return confirm(\''.e(__('msg.confirm_delete')).''.e(__('msg.zh_ask')).'?\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'bg-red-500 text-white rounded-md hover:bg-red-600 transition !p-1 !m-0 !my-auto','type' => 'submit','onclick' => 'return confirm(\''.e(__('msg.confirm_delete')).''.e(__('msg.zh_ask')).'?\')']); ?>
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
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php if (isset($component)) { $__componentOriginal9e35a3a693ed19f0f77d7aa00cae4f93 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9e35a3a693ed19f0f77d7aa00cae4f93 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.user_create-modal','data' => ['roles' => $roles]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.user_create-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['roles' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roles)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9e35a3a693ed19f0f77d7aa00cae4f93)): ?>
<?php $attributes = $__attributesOriginal9e35a3a693ed19f0f77d7aa00cae4f93; ?>
<?php unset($__attributesOriginal9e35a3a693ed19f0f77d7aa00cae4f93); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9e35a3a693ed19f0f77d7aa00cae4f93)): ?>
<?php $component = $__componentOriginal9e35a3a693ed19f0f77d7aa00cae4f93; ?>
<?php unset($__componentOriginal9e35a3a693ed19f0f77d7aa00cae4f93); ?>
<?php endif; ?>
            <?php if (isset($component)) { $__componentOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.user_edit-modal','data' => ['roles' => $roles]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.user_edit-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['roles' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($roles)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20)): ?>
<?php $attributes = $__attributesOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20; ?>
<?php unset($__attributesOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20)): ?>
<?php $component = $__componentOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20; ?>
<?php unset($__componentOriginal2a4bbfbe4be56372e5a0f7e7ae1d7f20); ?>
<?php endif; ?> <!-- 傳遞 roles 變數 -->
        </div>
    </div>

    <script>
        function checkRole(modalType) {
            let selectRole, filter;
            if (modalType === 'create') {
                selectRole = document.querySelector('#selectRole');
                filter = document.querySelector('#filter');
            } else if (modalType === 'edit') {
                selectRole = document.querySelector('#selectRoleEdit');
                filter = document.querySelector('#filterEdit');
            }
            if (selectRole && filter) {
                filter.style.display =(selectRole.value === 'admin') ? 'none' : 'block';
                selectRole.addEventListener('change', function() {
                    filter.style.display =(selectRole.value === 'admin') ? 'none' : 'block';
                }, { passive: true });
            }
            
        }
    </script>
<?php $__env->stopSection(); ?>
<?php
    $title = __('msg.user_management');
?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/users/index.blade.php ENDPATH**/ ?>