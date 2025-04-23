<?php $__env->startSection('content'); ?>
    <div class="flex justify-center bg-gray-100 dark:bg-gray-900">
        <div class="relative w-full bg-white bg-opacity-60 dark:bg-gray-900 dark:bg-opacity-70 shadow-lg rounded-lg">
            <div class="flex justify-between items-center mb-2 px-6 pt-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mx-4">
                    <?php echo e(__('msg.token_management')); ?>

                </h2>
                <div class="flex items-center">
                    <form id="filterForm" action="<?php echo e(route('chips.index')); ?>" method="GET" class="mr-4">
                        <select name="filter" id="filter" class="border rounded-md px-2 py-1" onchange="document.getElementById('filterForm').submit();">
                            <option value="all" <?php echo e($filter == 'all' ? 'selected' : ''); ?>><?php echo e(__('msg.all')); ?></option>
                            <option value="used" <?php echo e($filter == 'used' ? 'selected' : ''); ?>><?php echo e(__('msg.used')); ?></option>
                            <option value="unused" <?php echo e($filter == 'unused' ? 'selected' : ''); ?>><?php echo e(__('msg.unused')); ?></option>
                        </select>
                    </form>
                    <form action="<?php echo e(route('chip.store')); ?>" method="POST" class="confirm-submit">
                        <?php echo csrf_field(); ?>
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
                            <?php echo e(__('msg.add_token')); ?>

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
            
            <div class="container mx-auto px-2 pb-3">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg">
                    <!-- Header Row -->
                    <div class="flex items-center border-b border-gray-200 dark:border-gray-700 text-sm font-medium text-gray-700 dark:text-gray-100 shadow-lg">
                        <div class="w-[4%] break-words items-center text-center">ID</div>
                        <div class="w-[33%] break-words items-center text-center"><?php echo e(__('msg.token')); ?></div>
                        <div class="w-[20%] break-words items-center text-center"><?php echo e(__('msg.expires_at')); ?></div>
                        <div class="w-[13%] break-words items-center text-center"><?php echo e(__('msg.status')); ?></div>
                        <div class="w-[10%] break-words items-center text-center"><?php echo e(__('msg.creator')); ?></div>
                        <div class="w-[11%] px-1 text-center"><?php echo e(__('msg.actions')); ?></div>
                        <button type="button" id="printButton" class="w-[9%] qrcodeprint px-1 text-center text-xs items-center rounded-lg p-2 bg-yellow-200 dark:bg-yellow-800 hover:bg-yellow-300" onclick="handlePrintClick()">
                            列印
                        </button>
                    </div>
                </div>
                <!-- Data Rows -->
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] sm:max-h-[calc(100vh-150px)]">
                    <?php $__currentLoopData = $chipKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $chipKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center my-4 mx-0 border-b border-gray-200 text-sm font-medium text-gray-700 dark:text-gray-100">
                            <div class="w-[4%] px-1"><?php echo e($loop->iteration); ?></div>
                            <div class="w-[33%] px-1 break-words cursor-copy" id="chipKey" onclick="copyToClipboardAndGenerateLink('<?php echo e($chipKey->key); ?>', 'chip')">
                                <?php echo e($chipKey->key); ?>

                            </div>
                            <div class="w-[20%]"><?php echo e(\Carbon\Carbon::parse($chipKey->expires_at)->format('ymd/Hi')); ?></div>
                            <div class="w-[13%] px-1 text-center">
                                <?php if($chipKey->status == 'used'): ?>
                                    <span class="text-red-500"><?php echo e(__('msg.used')); ?></span>
                                <?php else: ?>
                                    <span class="text-green-500"><?php echo e(__('msg.unused')); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="w-[10%] px-1 text-center"><?php echo e($chipKey->creator->name ?? 'Unknown'); ?></div>
                            <div class="w-[11%] px-1 text-center">
                                <form action="<?php echo e(route('chip.destroy', $chipKey->id)); ?>" method="POST" class="inline-block">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-end px-2 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition" 
                                            onclick="return confirm('<?php echo e(__('msg.confirm_delete')); ?>');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </form>
                            </div>
                            <div class="w-[9%] break-words text-center">
                                <input type="checkbox" name="selected_ids[]" value="<?php echo e($chipKey->id); ?>" class="form-checkbox h-5 w-5 text-blue-600">
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php
    $title = __('msg.chip_token');
?>
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/chip/index.blade.php ENDPATH**/ ?>