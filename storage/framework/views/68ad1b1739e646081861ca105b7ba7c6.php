<div>
    <div class="flex flex-col items-top justify-between">
        <div class="block_header w-full pb-4">
            <h3 class="title" id="message-heading">
                <?php echo app('translator')->get('Export Comments'); ?>
            </h3>
            <p class="description pb-8 lg:pb-0">
                <?php echo app('translator')->get('If desired, select additional fields for identification in addition to the ID.' ); ?>
            </p>
        </div>

         <div class="block md:flex md:flex-wrap w-full space-x-4">
            <div class="opacity-40">
                <input type="checkbox" checked name="identifiers.device_id" class="appearance-none hidden h-0 w-0">
                <label for="identifiers.equipment" class="cursor-not-allowed group w-full flex items-center justify-between h-8 px-0 py-0 text-sm">

                    <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full bg-color-new text-white truncate uppercase select-none"><?php echo e('Device ID'); ?></div>
                    <div class="flex justify-center bg-color-new h-full w-12 items-center  px-2 border-none rounded-none rounded-r-full"><i class="f7-icons text-white text-sm">checkmark_alt</i></div>
                </label>
            </div>

             <?php $__currentLoopData = $identifiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $identifier => $selected): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                 <div wire:model="identifiers.<?php echo e($identifier); ?>">
                     <input type="checkbox" <?php if($selected): ?> checked <?php endif; ?> name="identifiers.<?php echo e($identifier); ?>" class="appearance-none hidden h-0 w-0">
                     <label wire:click="toggleIdentifier('<?php echo e($identifier); ?>')" for="identifiers.<?php echo e($identifier); ?>" class="group cursor-pointer w-full flex items-center justify-between h-8 px-0 py-0 text-sm">

                         <div class="py-1 pl-4 pr-2 w-full h-full flex items-center border-none rounded-none rounded-l-full <?php if($selected): ?> bg-color-new text-white <?php else: ?> bg-white <?php endif; ?> truncate uppercase select-none"><?php echo e($identifier); ?></div>
                         <?php if($selected): ?>
                             <div class="flex justify-center bg-color-new h-full w-12 items-center  px-2 border-none rounded-none rounded-r-full"><i class="f7-icons text-white text-sm">checkmark_alt</i></div>
                         <?php else: ?>
                             <div class="flex justify-center bg-color-new h-full w-12 items-center px-2 border-none rounded-none rounded-r-full text-white">&nbsp;</div>
                         <?php endif; ?>
                     </label>
                 </div>
             <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>













        </div>

        <div class="flex justify-end mt-4">
            <div x-data="{ showFormat: false }" class="relative z-10">
                <?php if (isset($component)) { $__componentOriginal79c47ff43af68680f280e55afc88fe59 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal79c47ff43af68680f280e55afc88fe59 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.primary','data' => ['xOn:click' => 'showFormat = true','class' => 'ml-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.primary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['x-on:click' => 'showFormat = true','class' => 'ml-4']); ?>
                    <?php echo app('translator')->get('export'); ?>
                 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $attributes = $__attributesOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__attributesOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal79c47ff43af68680f280e55afc88fe59)): ?>
<?php $component = $__componentOriginal79c47ff43af68680f280e55afc88fe59; ?>
<?php unset($__componentOriginal79c47ff43af68680f280e55afc88fe59); ?>
<?php endif; ?>

                <!-- Format Selection Dropdown -->
                <div
                    x-show="showFormat"
                    x-on:click.away="showFormat = false"
                    class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100"
                >
                    <div class="py-1">
                        <button
                            wire:click="$set('exportFormat', 'csv')"
                            x-on:click="showFormat = false; $wire.doExportComments()"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700"
                        >
                            CSV
                        </button>
                        <button
                            wire:click="$set('exportFormat', 'xlsx')"
                            x-on:click="showFormat = false; $wire.doExportComments()"
                            class="w-full text-left px-4 py-2 text-sm text-gray-700"
                        >
                            Excel (XLSX)
                        </button>
                    </div>
                </div>
            </div>

            <?php if (isset($component)) { $__componentOriginal36263f9a6b42b4504b8be98f2116ea00 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.button.secondary','data' => ['class' => 'ml-4','xOn:click' => '$dispatch(\'dropdown-select\', { element: \'\' })']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('button.secondary'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'ml-4','x-on:click' => '$dispatch(\'dropdown-select\', { element: \'\' })']); ?>
                <?php echo app('translator')->get('cancel'); ?>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $attributes = $__attributesOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__attributesOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00)): ?>
<?php $component = $__componentOriginal36263f9a6b42b4504b8be98f2116ea00; ?>
<?php unset($__componentOriginal36263f9a6b42b4504b8be98f2116ea00); ?>
<?php endif; ?>
        </div>


    </div>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/ucp/export-comments-new.blade.php ENDPATH**/ ?>