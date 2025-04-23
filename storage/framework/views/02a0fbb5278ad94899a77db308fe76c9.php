<a href="<?php echo e(route($route)); ?>" class="flex items-center px-2 
          sm:rounded-lg sm:shadow 
          w-full sm:w-auto whitespace-nowrap sm:whitespace-normal 
          hover:border-b-4 sm:hover:border-b-0 sm:hover:border-r-4
          hover:border-indigo-400
          hover:bg-indigo-100 dark:hover:bg-indigo-700
          active:bg-indigo-100 dark:active:bg-indigo-700
          sm:my-1 
    <?php if(request()->routeIs($route)): ?> 
        bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white
    <?php else: ?>
        bg-gray-100 dark:bg-gray-900 
        
    <?php endif; ?>">
    <?php if (isset($component)) { $__componentOriginal511d4862ff04963c3c16115c05a86a9d = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal511d4862ff04963c3c16115c05a86a9d = $attributes; } ?>
<?php $component = Illuminate\View\DynamicComponent::resolve(['component' => 'icon.'.$icon] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('dynamic-component'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\DynamicComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-6 w-6 @if(request()->routeIs($route)) text-gray-700 dark:text-gray-200 @else text-gray-500 dark:text-gray-100 @endif']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $attributes = $__attributesOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__attributesOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal511d4862ff04963c3c16115c05a86a9d)): ?>
<?php $component = $__componentOriginal511d4862ff04963c3c16115c05a86a9d; ?>
<?php unset($__componentOriginal511d4862ff04963c3c16115c05a86a9d); ?>
<?php endif; ?>
    <div class="max-w-48 overflow-hidden pl-3 sm:py-1 ">
        <p class="text-base font-medium <?php if(request()->routeIs($route)): ?> dark:text-white <?php else: ?> text-gray-900 dark:text-gray-100 <?php endif; ?>">
            <?php echo e($title); ?>

        </p>
        <small class="hidden sm:block mt-1 text-xs <?php if(request()->routeIs($route)): ?> dark:text-white <?php else: ?> text-gray-500 dark:text-gray-400 <?php endif; ?>">
            <?php echo e($description); ?>

        </small>
    </div>
</a>

<?php /**PATH /www/wwwroot/syswaw/resources/views/components/modal/sidebar-link.blade.php ENDPATH**/ ?>