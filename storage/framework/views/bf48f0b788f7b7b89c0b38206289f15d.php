<div wire:poll.8s="updateAlarmCalls"
     class="flex justify-end px-8 text-gray-600 <?php if(count($alarmCalls) == 0): ?> text-opacity-20 <?php endif; ?>"
     style="z-index: 99999;">
    <?php if(count($alarmCalls) == 0): ?>
        <?php if (isset($component)) { $__componentOriginal9791e0b679eecbc88c7e2a2e321623af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9791e0b679eecbc88c7e2a2e321623af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.icon','data' => ['icon' => 'bell_slash','color' => 'disabled','size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('bell_slash'),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('disabled'),'size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('xl')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9791e0b679eecbc88c7e2a2e321623af)): ?>
<?php $attributes = $__attributesOriginal9791e0b679eecbc88c7e2a2e321623af; ?>
<?php unset($__attributesOriginal9791e0b679eecbc88c7e2a2e321623af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9791e0b679eecbc88c7e2a2e321623af)): ?>
<?php $component = $__componentOriginal9791e0b679eecbc88c7e2a2e321623af; ?>
<?php unset($__componentOriginal9791e0b679eecbc88c7e2a2e321623af); ?>
<?php endif; ?>
        
    <?php else: ?>
        <div class="relative">
            <div class="relative px-0 dropdown cursor-pointer" x-data="{ open: false }"
                 x-on:keydown.window.escape="open = false" x-on:click.away="open = false" class="dropdown">
                <div x-on:click="open = !open" class="with_text" id="options-menu" aria-haspopup="true"
                     x-bind:aria-expanded="open" aria-expanded="true">
                    <div wire:model="alarmCalls"
                         class="absolute bottom-auto left-auto right-0 top-0 z-10 mt-2 rotate-0 skew-x-0 skew-y-0 scale-x-100 scale-y-100 rounded-full bg-red-700 text-white p-0.5 w-5 h-5 flex items-center justify-center text-xs"><?php if(count($alarmCalls) > 0): ?>
                            <?php echo e(count($alarmCalls)); ?>

                        <?php endif; ?></div>
                    <?php if (isset($component)) { $__componentOriginal9791e0b679eecbc88c7e2a2e321623af = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9791e0b679eecbc88c7e2a2e321623af = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.icon','data' => ['icon' => 'bell_fill','color' => 'blue','size' => 'xl']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('form.icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('bell_fill'),'color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('blue'),'size' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('xl')]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9791e0b679eecbc88c7e2a2e321623af)): ?>
<?php $attributes = $__attributesOriginal9791e0b679eecbc88c7e2a2e321623af; ?>
<?php unset($__attributesOriginal9791e0b679eecbc88c7e2a2e321623af); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9791e0b679eecbc88c7e2a2e321623af)): ?>
<?php $component = $__componentOriginal9791e0b679eecbc88c7e2a2e321623af; ?>
<?php unset($__componentOriginal9791e0b679eecbc88c7e2a2e321623af); ?>
<?php endif; ?>
                    <div
                            class="origin-top-right absolute right-0 mt-1 shadow-md"
                            x-show="open"
                            style="display: none; width: 59rem;"
                            x-on:click="open = false">
                        <div class="bg-white shadow-md border border-gray-300 absolute top-auto left-0 min-w-full z-40 "
                             x-show="open" style="display: none; width: 68rem;">
                            <div class="bg-white w-full relative z-40 py-1">
                                <ul class="list-reset divide-y divide-gray-200">
                                    <?php $__currentLoopData = $alarmCalls; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alarmCallDevice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="relative">

                                            <?php
                                                $address = $alarmCallDevice['device_site']['address']['in_one_line'] ?? null;
                                                $equipment = $alarmCallDevice['device_equipment'] ?? null;
                                                $line = ($equipment && $address) ? ($equipment.', '.$address) : ($equipment ?? $address ?? '');

                                                $phoneType = $alarmCallDevice['device_site']['single_number']['type'] ?? null;
                                                $phoneLine = $phoneType ? ucfirst($phoneType).':' : '';
                                            ?>

                                            
                                            <a href="/callcenter/<?php echo e($alarmCallDevice['device_id']); ?>" class="group px-4 py-2 flex justify-between items-center hover:bg-color-new hover:text-white no-underline hover:no-underline transition-colors duration-100 cursor-pointer">
                                                <span class="flex"><span class="text-gray-400 group-hover:text-white"><?php echo e(__('Equipment')); ?>:&nbsp;</span><span class="text-gray-500 group-hover:text-white"><?php echo e($line); ?></span></span>
                                                <span class="flex">
                                                    <span class="text-gray-400 group-hover:text-white w-11"><?php echo e($phoneLine); ?></span>
                                                    <span class="flex text-gray-500 group-hover:text-white w-32">
                                                        <?php echo e($alarmCallDevice['device_site']['single_number']['value'] ?? ''); ?>

                                                    </span>
                                                    <?php if (isset($component)) { $__componentOriginalda701eee059a25cdcbd3983dd07619f1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalda701eee059a25cdcbd3983dd07619f1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.monoicon.chevron-right','data' => ['class' => 'ml-2 text-gray-400 group-hover:text-white']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('monoicon.chevron-right'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'ml-2 text-gray-400 group-hover:text-white']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalda701eee059a25cdcbd3983dd07619f1)): ?>
<?php $attributes = $__attributesOriginalda701eee059a25cdcbd3983dd07619f1; ?>
<?php unset($__attributesOriginalda701eee059a25cdcbd3983dd07619f1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalda701eee059a25cdcbd3983dd07619f1)): ?>
<?php $component = $__componentOriginalda701eee059a25cdcbd3983dd07619f1; ?>
<?php unset($__componentOriginalda701eee059a25cdcbd3983dd07619f1); ?>
<?php endif; ?>
                                                </span>
                                            </a>

                                            




                                        </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/admin/alarm-notification.blade.php ENDPATH**/ ?>