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
<?php if (isset($component)) { $__componentOriginal174b036c3c97a29c1535c8ef587942cf = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal174b036c3c97a29c1535c8ef587942cf = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon.machines','data' => ['class' => $class]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('icon.machines'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($class)]); ?>

<?php echo e($slot ?? ""); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal174b036c3c97a29c1535c8ef587942cf)): ?>
<?php $attributes = $__attributesOriginal174b036c3c97a29c1535c8ef587942cf; ?>
<?php unset($__attributesOriginal174b036c3c97a29c1535c8ef587942cf); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal174b036c3c97a29c1535c8ef587942cf)): ?>
<?php $component = $__componentOriginal174b036c3c97a29c1535c8ef587942cf; ?>
<?php unset($__componentOriginal174b036c3c97a29c1535c8ef587942cf); ?>
<?php endif; ?><?php /**PATH /www/wwwroot/syswaw/storage/framework/views/d6582443a6be89ef0c04d4ebe25454d8.blade.php ENDPATH**/ ?>