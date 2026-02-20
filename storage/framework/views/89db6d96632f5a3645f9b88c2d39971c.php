<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['hasUpdate' => false]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['hasUpdate' => false]); ?>
<?php foreach (array_filter((['hasUpdate' => false]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>
<div <?php echo $attributes->merge([
    'class' =>
        'px-4 sm:px-6 lg:px-8'
]); ?>>
    <div  class=" <?php if($hasUpdate): ?>relative blur-lg cursor-not-allowed pointer-events-none <?php endif; ?> overflow-hidden shadow-lg bg-gray-300 bg-opacity-20">
        <div class="flex flex-col max md:flex-row md:flex-1 lg:max-w-screen-md">
            <div class="p-8 md:w-1/2 md:flex-shrink-0 md:flex md:flex-col items-start">
                <a href="https://serv24.com/en/solutions/ucp">
                    <h3 class="mb-4"><?php echo app('translator')->get('UCP'); ?></h3>
                    <span class="text-base"><?php echo app('translator')->get('Universal Convergence Platform'); ?></span>
                </a>
                <p class="mt-6 opacity-70 md:mt-0">
                    <?php echo app('translator')->get('Cloud based alarm call handling system with multivendor alarm device support for PSTN and IP based communication. Meets the industry-specific requirements of EN81-28.'); ?>
                </p>
                <p class="flex flex-col mt-8">
                    <span>
                        <?php echo app('translator')->get('You have no account?'); ?>
                    </span>
                    <span>
                        <?php echo app('translator')->get('Do not hesitate and contact us today!'); ?> <a class="underline" href="sales@serv24.com">sales@serv24.com</a>
                    </span>
                </p>
            </div>
            <div class="p-8 md:flex-1">
                <h3 class="mb-4">
                    <?php echo e($title ?? ''); ?>

                </h3>
                <?php echo e($slot); ?>

            </div>
        </div>
        <div class="w-full px-8 py-1 ">
            <div class="w-full border-t border-gray-400"></div>
            <div class="text-xs float-right inline-block items-end my-4">
                <a class="" href="https://serv24.com/terms">
                    <?php echo app('translator')->get('Terms & Conditions'); ?>
                </a>
            </div>

        </div>
    </div>
</div>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/auth/card.blade.php ENDPATH**/ ?>