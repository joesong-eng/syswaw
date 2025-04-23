<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
        <?php if (isset($component)) { $__componentOriginal791d26948561d5a0da3d85fee400a7b6 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal791d26948561d5a0da3d85fee400a7b6 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.welcome','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('welcome'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal791d26948561d5a0da3d85fee400a7b6)): ?>
<?php $attributes = $__attributesOriginal791d26948561d5a0da3d85fee400a7b6; ?>
<?php unset($__attributesOriginal791d26948561d5a0da3d85fee400a7b6); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal791d26948561d5a0da3d85fee400a7b6)): ?>
<?php $component = $__componentOriginal791d26948561d5a0da3d85fee400a7b6; ?>
<?php unset($__componentOriginal791d26948561d5a0da3d85fee400a7b6); ?>
<?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php
    $title =  __('msg.dashboard.01') ;
?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /www/wwwroot/syswaw/resources/views/dashboard.blade.php ENDPATH**/ ?>