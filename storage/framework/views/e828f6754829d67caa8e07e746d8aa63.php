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
         <?php $__env->slot('title', null, []); ?> <?php echo app('translator')->get('Choose Language'); ?> <?php $__env->endSlot(); ?>
         <?php $__env->slot('description', null, []); ?> <?php echo app('translator')->get('Choose language that will be applied for your user'); ?> <?php $__env->endSlot(); ?>
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

    <div class="w-full pt-2">
        <fieldset class="-mx-1 mb-1">

            <div class="flex w-full">
                <?php $__currentLoopData = $languagesFromDB; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $languageFromDB): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                    <?php if(empty($languageFromDB['language_enabled'])) continue; ?>
                    <?php if(empty($languagesFromJson[$languageFromDB['language_code']])) continue; ?>

                    <div class="flex justify-center">
                        <?php if (isset($component)) { $__componentOriginal238ef69521260bea04102fc9c8edbb9f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal238ef69521260bea04102fc9c8edbb9f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.nav.button-horizontal-new','data' => ['active' => $languageFromDB['language_code'] == App::getLocale(),'href' => route('lang.switch', $languageFromDB['language_code'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('nav.button-horizontal-new'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['active' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($languageFromDB['language_code'] == App::getLocale()),'href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('lang.switch', $languageFromDB['language_code']))]); ?>
                            <span><?php echo e(flag("{$languageFromDB['flag']}")); ?></span>
                            <?php echo e($languageNames[$languageFromDB['language_code']]); ?>

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
</div><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/livewire/user/change-language.blade.php ENDPATH**/ ?>