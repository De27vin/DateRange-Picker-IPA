<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['value', 'required' => "false"]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['value', 'required' => "false"]); ?>
<?php foreach (array_filter((['value', 'required' => "false"]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<label <?php echo e($attributes->merge(['class' => ''])); ?>>
    <?php echo e($value ?? $slot); ?>

    <?php if($required): ?>
        <i class="absolute right-0 px-4 text-xs fa fa-star text-red-600"></i>
    <?php endif; ?>
</label>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/label.blade.php ENDPATH**/ ?>