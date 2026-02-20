<div class="mx-auto pb-12 px-4 mt-8">

    <?php if (isset($component)) { $__componentOriginal4a29ad41492c717286123b97d5ba8cca = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4a29ad41492c717286123b97d5ba8cca = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page.header','data' => ['class' => 'h-24']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('page.header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'h-24']); ?>
         <?php $__env->slot('title', null, []); ?> <?php echo app('translator')->get('Default filters'); ?> <?php $__env->endSlot(); ?>
         <?php $__env->slot('description', null, []); ?> <?php echo app('translator')->get('Choose default active filter tab for device lists'); ?> <?php $__env->endSlot(); ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4a29ad41492c717286123b97d5ba8cca)): ?>
<?php $attributes = $__attributesOriginal4a29ad41492c717286123b97d5ba8cca; ?>
<?php unset($__attributesOriginal4a29ad41492c717286123b97d5ba8cca); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4a29ad41492c717286123b97d5ba8cca)): ?>
<?php $component = $__componentOriginal4a29ad41492c717286123b97d5ba8cca; ?>
<?php unset($__componentOriginal4a29ad41492c717286123b97d5ba8cca); ?>
<?php endif; ?>

    <div class="ml-9 w-full pt-2">
        <fieldset class="-mx-1 mb-1">
            <div class="flex w-full items-center">
                <span class="text-medium w-48"><?php echo app('translator')->get('Dashboard default:'); ?></span>
                <?php $__currentLoopData = $defaultDashboardFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-center w-36">
                        <?php if (isset($component)) { $__componentOriginal238ef69521260bea04102fc9c8edbb9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal238ef69521260bea04102fc9c8edbb9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.nav.button-horizontal-new','data' => ['uppercase' => true,'active' => $active,'click' => 'updateDashboardFilter(\''.e($filter).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('nav.button-horizontal-new'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['uppercase' => true,'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($active),'click' => 'updateDashboardFilter(\''.e($filter).'\')']); ?>
                            <?php echo app('translator')->get($filter); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal238ef69521260bea04102fc9c8edbb9f)): ?>
<?php $attributes = $__attributesOriginal238ef69521260bea04102fc9c8edbb9f; ?>
<?php unset($__attributesOriginal238ef69521260bea04102fc9c8edbb9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal238ef69521260bea04102fc9c8edbb9f)): ?>
<?php $component = $__componentOriginal238ef69521260bea04102fc9c8edbb9f; ?>
<?php unset($__componentOriginal238ef69521260bea04102fc9c8edbb9f); ?>
<?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex w-full items-center">
                <span class="text-medium w-48"><?php echo app('translator')->get('Equipment default:'); ?></span>
                <?php $__currentLoopData = $defaultEquipmentFilters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter => $active): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex justify-center w-36">
                        <?php if (isset($component)) { $__componentOriginal238ef69521260bea04102fc9c8edbb9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal238ef69521260bea04102fc9c8edbb9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.nav.button-horizontal-new','data' => ['uppercase' => true,'active' => $active,'click' => 'updateEquipmentFilter(\''.e($filter).'\')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('nav.button-horizontal-new'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['uppercase' => true,'active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($active),'click' => 'updateEquipmentFilter(\''.e($filter).'\')']); ?>
                            <?php echo app('translator')->get($filter); ?>
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal238ef69521260bea04102fc9c8edbb9f)): ?>
<?php $attributes = $__attributesOriginal238ef69521260bea04102fc9c8edbb9f; ?>
<?php unset($__attributesOriginal238ef69521260bea04102fc9c8edbb9f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal238ef69521260bea04102fc9c8edbb9f)): ?>
<?php $component = $__componentOriginal238ef69521260bea04102fc9c8edbb9f; ?>
<?php unset($__componentOriginal238ef69521260bea04102fc9c8edbb9f); ?>
<?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

        </fieldset>

    </div>
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/user/change-filters.blade.php ENDPATH**/ ?>