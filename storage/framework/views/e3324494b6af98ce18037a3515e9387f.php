<?php $__env->startSection('content'); ?>
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <!-- 新增金鑰按鈕 -->
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    <?php echo e(__('msg.token_management')); ?>

                </h2>
                <form action="<?php echo e(route('admin.keyStore')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginald0f1fd2689e4bb7060122a5b91fe8561 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginald0f1fd2689e4bb7060122a5b91fe8561 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button','data' => ['type' => 'submit']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit']); ?>
                    <?php echo e(__('msg.create')); ?> <?php echo e(__('msg.token')); ?> <?php echo $__env->renderComponent(); ?>
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
            
            <div class="container mx-auto px-2 pb-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-start border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg">
                        <div class="w-[4%]  px-1 border-r">#</div>
                        <div class="w-[35%] break-words">金鑰</div>
                        <div class="w-[15%] text-center"><?php echo e(__('msg.used')); ?></div>
                        <div class="w-[16%] text-center"><?php echo e(__('msg.creator')); ?></div>
                        <div class="w-[16%] text-center border-l"><?php echo e(__('msg.expires_at')); ?></div>
                        <div class="w-[9%]  text-center "><?php echo e(__('msg.actions')); ?></div>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                    <?php $__currentLoopData = $arcadeKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arcadeKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center my-4 mx-0 border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100">
                            <div class="w-[4%] px-1"><?php echo e($loop->iteration); ?></div> 
                            <?php if($arcadeKey->used): ?>
                                <div class="w-[35%] px-1 break-words cursor-default truncate" id="arcadeKey" title="<?php echo e($arcadeKey->token); ?>"> 
                            <?php else: ?>
                                
                                <div class="w-[35%] px-1 break-words cursor-copy truncate" id="arcadeKey" 
                                onclick="copyToClipboardAndGenerateLink('<?php echo e($arcadeKey->token); ?>','arcades')">
                            <?php endif; ?><?php echo e($arcadeKey->token); ?></div>
                            <div class="w-[15%] px-1 text-center">
                                <?php if($arcadeKey->used): ?>
                                    <span class="text-bule-500">
                                        <div class="text-xs items-start">
                                            <!-- 顯示綁定的 Arcade 名稱 -->
                                            <div class="font-thin" style="font-size: xx-small; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?php if($arcadeKey->arcade): ?>已綁定: <?php echo e($arcadeKey->arcade->name); ?><?php else: ?> 未綁定任何娛樂城<?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="text-red-500" style="white-space: nowrap; ellipsis;">已使用</div>
                                    </span>
                                <?php else: ?>
                                    <span class="text-green-500">未使用</span>
                                <?php endif; ?>
                            </div>
                            
                            
                            <div class="w-[16%] px-1 items-center  text-center break-words"><?php echo e($arcadeKey->creator->name ?? 'Unknown'); ?></div>
                            <div class="w-[16%] items-center  text-center font-thin text-xs break-words"><?php echo e(\Carbon\Carbon::parse($arcadeKey->expires_at)->format('ymd/Hi')); ?></div>
                            <div class="w-[9%] break-words text-center m-auto space-x-1 border-l">
                                <?php if(!$arcadeKey->arcade): ?>
                                <form action="<?php echo e(route('arcade.keyDestroy', $arcadeKey)); ?>" method="POST" class="inline-block">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-end px-2 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition" 
                                            onclick="return confirm('<?php echo e(__('msg.confirm_delete')); ?>');">
                                            <?php if (isset($component)) { $__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon.trash','data' => ['class' => 'h-6 w-6 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icon.trash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8)): ?>
<?php $attributes = $__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8; ?>
<?php unset($__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8)): ?>
<?php $component = $__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8; ?>
<?php unset($__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8); ?>
<?php endif; ?>
                                    </button>
                                </form>
                                <?php else: ?>
                                <!-- 按钮 -->
                                <button type="button"
                                        class="text-end px-2 py-2 bg-gray-500 text-white rounded-md transition cursor-default"
                                        onclick="showLocalMessage()">
                                    <?php if (isset($component)) { $__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon.trash','data' => ['class' => 'h-6 w-6 text-gray-500']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icon.trash'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 text-gray-500']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8)): ?>
<?php $attributes = $__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8; ?>
<?php unset($__attributesOriginal1bc295e5c424e8fa8f76ad875cdf51d8); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8)): ?>
<?php $component = $__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8; ?>
<?php unset($__componentOriginal1bc295e5c424e8fa8f76ad875cdf51d8); ?>
<?php endif; ?>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

<?php $__env->stopSection(); ?>
<?php
    $title = __('msg.arcade_key_management');
?>
   

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/admin/arcade/arcadeKey.blade.php ENDPATH**/ ?>