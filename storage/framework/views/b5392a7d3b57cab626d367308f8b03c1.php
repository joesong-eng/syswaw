<?php extract(collect($attributes->getAttributes())->mapWithKeys(function ($value, $key) { return [Illuminate\Support\Str::camel(str_replace([':', '.'], ' ', $key)) => $value]; })->all(), EXTR_SKIP); ?>
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['class']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['class']); ?>
<?php foreach (array_filter((['class']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php if (isset($component)) { $__componentOriginal877da9610ea27f73f0f8f9d5d01fbd9b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal877da9610ea27f73f0f8f9d5d01fbd9b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon.key','data' => ['class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icon.key'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class)]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal877da9610ea27f73f0f8f9d5d01fbd9b)): ?>
<?php $attributes = $__attributesOriginal877da9610ea27f73f0f8f9d5d01fbd9b; ?>
<?php unset($__attributesOriginal877da9610ea27f73f0f8f9d5d01fbd9b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal877da9610ea27f73f0f8f9d5d01fbd9b)): ?>
<?php $component = $__componentOriginal877da9610ea27f73f0f8f9d5d01fbd9b; ?>
<?php unset($__componentOriginal877da9610ea27f73f0f8f9d5d01fbd9b); ?>
<?php endif; ?><?php /**PATH /www/wwwroot/syswaw/storage/framework/views/a1f4d40d5a0532ab5c3215f925eb319d.blade.php ENDPATH**/ ?>