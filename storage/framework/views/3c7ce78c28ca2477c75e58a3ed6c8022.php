<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['active', 'href' => '#', 'click' => '', 'border' => false, 'uppercase' => false]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['active', 'href' => '#', 'click' => '', 'border' => false, 'uppercase' => false]); ?>
<?php foreach (array_filter((['active', 'href' => '#', 'click' => '', 'border' => false, 'uppercase' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $classes = ($active ?? false)
        ? 'rounded-full cursor-pointer px-1 flex items-center py-2 leading-5 text-white bg-color-new hover:text-white hover:bg-color-new'
        : 'rounded-full cursor-pointer px-1 flex items-center py-2 leading-5 text-secondary-600 hover:text-gray-200 hover:bg-color-new focus:text-gray-200 focus:bg-color-new' . ($border ? ' border border-slate-300' : '');
?>

<div class="px-0 flex flex-row space-x-4">
    <button <?php echo e($attributes->merge(['type' => 'button', 'class' => $classes])); ?> role="menuitem">
        <?php if($click): ?>
            <span class="rounded-none justify-start text-left px-4 py-1 mx-0 u <?php if($uppercase): ?> uppercase <?php else: ?> normal-case <?php endif; ?>" wire:click="<?php echo e($click); ?>" style="display: flex; align-items: center; column-gap: 0.3rem;">
                <?php echo e($slot); ?>

            </span>
        <?php else: ?>
            <a class="rounded-none justify-start text-left px-4 py-1 mx-0 <?php if($uppercase): ?> uppercase <?php else: ?> normal-case <?php endif; ?>" href="<?php echo e($href); ?>" style="display: flex; align-items: center; column-gap: 0.3rem;">
                <?php echo e($slot); ?>

            </a>
        <?php endif; ?>
    </button>
</div>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/nav/button-horizontal-new.blade.php ENDPATH**/ ?>