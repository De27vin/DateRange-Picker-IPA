<div wire:poll.visible.30s="updateDashboardStats" class="bottom-underline py-4">
    <div class="grid h-72 sm:h-56 md:h-40 lg:h-28 sm:grid-flow-row gap-4 sm:gap-4 grid-cols-2 sm:grid-cols-3 lg:grid-cols-5">

        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statitem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex relative" style="background-color: <?php echo e($statitem['color']); ?>;">
                <div class="relative h-full w-full leading-tight">

                    <div class="h-full flex flex-col justify-between pb-1">
                        <div class="py-2 px-2 text-xl" style="color: <?php echo e($statitem['text-color']); ?>;" ><?php echo app('translator')->get($statitem['label']); ?></div>

                        <div>
                            <?php $__currentLoopData = $statitem['values']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $title => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex pt-1 w-full text-base md:text-base justify-between">
                                    <div class="px-2"><?php echo e(__($title)); ?></div>
                                    <div class="px-2"><?php echo e($value); ?></div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/dashboard/stats.blade.php ENDPATH**/ ?>