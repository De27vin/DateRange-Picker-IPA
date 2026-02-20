<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'color' => 'default',
    'icon' => null,
    'type' => 'span',
    'rounded' => null,
    'id' => null,
    'link' => null,
    'size' => 'sm',
    'opacity' => true,
    'target' => null
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'color' => 'default',
    'icon' => null,
    'type' => 'span',
    'rounded' => null,
    'id' => null,
    'link' => null,
    'size' => 'sm',
    'opacity' => true,
    'target' => null
]); ?>
<?php foreach (array_filter(([
    'color' => 'default',
    'icon' => null,
    'type' => 'span',
    'rounded' => null,
    'id' => null,
    'link' => null,
    'size' => 'sm',
    'opacity' => true,
    'target' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<?php
    if($type == 'link'){
        $identifier = 'a';
    } else {
        $identifier = 'div';
    }
    if($opacity){
        $opacity = ' transparent ';
        $textcolor = '';
    } else {
        $opacity = ' full ';
        $textcolor = 'text-white';
    }
?>
<<?php echo e($identifier); ?> 
    class="f7-icons-wrapper <?php echo e($opacity); ?> <?php echo e($size); ?>"
    <?php if($link != null): ?> href="<?php echo e($link); ?>" <?php endif; ?> >
    <i class="f7-icons <?php echo e($size); ?>"><?php echo e($icon); ?></i>
</<?php echo e($identifier); ?>><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/form/icon.blade.php ENDPATH**/ ?>