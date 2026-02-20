<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'tabs' => [],
    'defaultTab' => '',
    'barMargin' => null
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'tabs' => [],
    'defaultTab' => '',
    'barMargin' => null
]); ?>
<?php foreach (array_filter(([
    'tabs' => [],
    'defaultTab' => '',
    'barMargin' => null
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div x-cloak x-data="{ openTab: '<?php echo e($defaultTab ?? ''); ?>' }" class="py-8">
    <div class="w-full">
        <div class="bottom-underline mb-4 flex justify-between bg-white bg-opacity-60" <?php if($barMargin): ?> style="margin-bottom: <?php echo e($barMargin); ?>;" <?php endif; ?>>
            <div class="flex max-w-2xl space-x-4 px-2 py-1 pr-4">
                <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button x-on:click="openTab = '<?php echo e($tabKey); ?>'"
                            :class="{ 'bg-color-new text-white': openTab === '<?php echo e($tabKey); ?>' }"
                            class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue hover:bg-color-new hover:text-white">
                        <?php echo e($tabLabel); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <div class="w-full mx-auto mt-6">
        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div x-show="openTab === '<?php echo e($tabKey); ?>'" class="w-full">
                <?php if(isset(${$tabKey.'Slot'})): ?>
                    <?php echo e(${$tabKey.'Slot'}); ?>

                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/page/tabs-primary.blade.php ENDPATH**/ ?>