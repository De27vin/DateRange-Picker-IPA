<?php if($title != ''): ?>
<div <?php echo e($attributes->merge(['class' => ''])); ?> >
    <div class="my-4 w-full text-sm flex justify-between bottom-underline" style="align-items: last baseline;">
        <div class="flex flex-col">
            <h1 class="text-lg text-medium mb-0 text-gray-900" id="message-heading">
                <?php echo e($title ?? ''); ?>

            </h1>
            <p class="mt-1 text-base text-gray-500 overflow-hidden overflow-ellipsis">
                <?php echo e($description ?? ''); ?>

            </p>
        </div>
        <div class="flex items-end sm:justify-end">
            <div class="relative pl-0 pr-1 mb-1">
                <?php echo e($slot); ?>

                <?php echo e($actionButtons ?? ''); ?>

            </div>
        </div>
    </div>
</div>
<?php endif; ?><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/page/header.blade.php ENDPATH**/ ?>