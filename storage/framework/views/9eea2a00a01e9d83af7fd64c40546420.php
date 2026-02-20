<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'leadingAddOn' => false,
    'rounded' => false,
    'canWriteSettings'=>true
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'leadingAddOn' => false,
    'rounded' => false,
    'canWriteSettings'=>true
]); ?>
<?php foreach (array_filter(([
    'leadingAddOn' => false,
    'rounded' => false,
    'canWriteSettings'=>true
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<input <?php echo e(($canWriteSettings ? '' : ' readonly=readonly ')); ?> <?php echo e($attributes->merge(['class' => 'h-16'])); ?> type="password" autocomplete="new-password" />
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/input/password.blade.php ENDPATH**/ ?>