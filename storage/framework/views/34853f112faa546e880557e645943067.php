<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'target' => '',
    'tabs' => [],
    'defaultTab' => '',
    'verticalSpace' => false,
    'buttonsOnTop' => false,
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'target' => '',
    'tabs' => [],
    'defaultTab' => '',
    'verticalSpace' => false,
    'buttonsOnTop' => false,
]); ?>
<?php foreach (array_filter(([
    'target' => '',
    'tabs' => [],
    'defaultTab' => '',
    'verticalSpace' => false,
    'buttonsOnTop' => false,
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<div x-cloak x-data="{ openTab: '<?php echo e($defaultTab ?? ''); ?>' }">
    <div class="">
        <div class="">
            <div class="flex justify-between items-end w-full space-x-4 <?php if($verticalSpace): ?> my-4 <?php endif; ?>">

                <div>
                    <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <button x-on:click="openTab = '<?php echo e($tabKey); ?>'"
                                :class="openTab === '<?php echo e($tabKey); ?>'? 'bg-color-new text-white' : 'bg-white text-gray-600 border border-slate-300'"
                                class="flex-1 py-2 focus:outline-none focus:shadow-outline-blue">
                            <?php echo e($tabLabel); ?>

                        </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <?php if($buttonsOnTop): ?>
                    <div>
                        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div x-show="openTab === '<?php echo e($tabKey); ?>'" wire:key="btn-slot-<?php echo e($target); ?>-<?php echo e($tabKey); ?>" class="w-full">
                                <?php echo e(${$tabKey.'Buttons'}); ?>

                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <div class="w-full mx-auto pt-4">
        <?php $__currentLoopData = $tabs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tabKey => $tabLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div x-show="openTab === '<?php echo e($tabKey); ?>'" wire:key="tab-slot-<?php echo e($target); ?>-<?php echo e($tabKey); ?>" class="w-full">
                <?php echo e(${$tabKey.'Slot'}); ?>

            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/page/tabs-secondary.blade.php ENDPATH**/ ?>