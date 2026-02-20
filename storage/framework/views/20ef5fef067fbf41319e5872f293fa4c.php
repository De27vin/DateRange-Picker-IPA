<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'label',
    'for',
    'error'       => false,
    'helpText'    => false,
    'inline'      => false,
    'paddingless' => false,
    'borderless'  => true,
    'rounded'     => false,
    'required'    => false,
    'readonly'    => false,
    'errNegMar'    => false,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'label',
    'for',
    'error'       => false,
    'helpText'    => false,
    'inline'      => false,
    'paddingless' => false,
    'borderless'  => true,
    'rounded'     => false,
    'required'    => false,
    'readonly'    => false,
    'errNegMar'    => false,
]); ?>
<?php foreach (array_filter(([
    'label',
    'for',
    'error'       => false,
    'helpText'    => false,
    'inline'      => false,
    'paddingless' => false,
    'borderless'  => true,
    'rounded'     => false,
    'required'    => false,
    'readonly'    => false,
    'errNegMar'    => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

    <div class="relative w-full">
        <label for="<?php echo e($for); ?>" class="default">
            <?php echo e(__($label)); ?>

            <?php if($readonly): ?>
                <div><?php if (isset($component)) { $__componentOriginalfc41d01ef67588805e1a7433b90b2cb0 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalfc41d01ef67588805e1a7433b90b2cb0 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.monoicon.locked','data' => ['class' => 'absolute top-0 right-0 pr-2 pt-2 text-red-600']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('monoicon.locked'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'absolute top-0 right-0 pr-2 pt-2 text-red-600']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalfc41d01ef67588805e1a7433b90b2cb0)): ?>
<?php $attributes = $__attributesOriginalfc41d01ef67588805e1a7433b90b2cb0; ?>
<?php unset($__attributesOriginalfc41d01ef67588805e1a7433b90b2cb0); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalfc41d01ef67588805e1a7433b90b2cb0)): ?>
<?php $component = $__componentOriginalfc41d01ef67588805e1a7433b90b2cb0; ?>
<?php unset($__componentOriginalfc41d01ef67588805e1a7433b90b2cb0); ?>
<?php endif; ?></div>
            <?php else: ?>
                <?php if($required): ?><?php if (isset($component)) { $__componentOriginalae99970fd62e84331b2c998b40bc7fbc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalae99970fd62e84331b2c998b40bc7fbc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.monoicon.required','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('monoicon.required'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalae99970fd62e84331b2c998b40bc7fbc)): ?>
<?php $attributes = $__attributesOriginalae99970fd62e84331b2c998b40bc7fbc; ?>
<?php unset($__attributesOriginalae99970fd62e84331b2c998b40bc7fbc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalae99970fd62e84331b2c998b40bc7fbc)): ?>
<?php $component = $__componentOriginalae99970fd62e84331b2c998b40bc7fbc; ?>
<?php unset($__componentOriginalae99970fd62e84331b2c998b40bc7fbc); ?>
<?php endif; ?><?php endif; ?>
            <?php endif; ?>    

        </label>

            <?php echo e($slot); ?>

            <?php if($error): ?>
                <div class="<?php if($errNegMar): ?> -mt-1 mb-1 <?php else: ?> mt-1 <?php endif; ?> py-1 px-2 text-white text-sm" style="background-color: #e297ac;"><?php echo app('translator')->get($error); ?></div>
            <?php endif; ?>

            <?php if($helpText): ?>
                <p class="mt-2 text-sm text-gray-500"><?php echo e($helpText); ?></p>
            <?php endif; ?>
    </div>

<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/input/group.blade.php ENDPATH**/ ?>