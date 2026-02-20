
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['programmableSettings', 'model', 'updateMethodName', 'target']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['programmableSettings', 'model', 'updateMethodName', 'target']); ?>
<?php foreach (array_filter((['programmableSettings', 'model', 'updateMethodName', 'target']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php uasort($programmableSettings, fn ($a, $b) => strcmp(strtolower($a['key']), strtolower($b['key']))); ?>

<?php if(count($programmableSettings)): ?>
    <form wire:submit.prevent.stop="<?php echo e($updateMethodName); ?>">
        <div class="md:flex flex-wrap mb-4">
            <?php $__currentLoopData = $programmableSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($setting['type'] == 'bool'): ?>
                    <?php if (isset($component)) { $__componentOriginal295ad6c56f7e489bae208d7528aad833 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal295ad6c56f7e489bae208d7528aad833 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.forms.group','data' => ['class' => 'mb-4 pl-4 flex items-center justify-between']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('forms.group'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['class' => 'mb-4 pl-4 flex items-center justify-between']); ?>
                            <span class="mr-3">
                                <span class="text-sm text-medium text-gray-800"><?php echo e($setting['translation']); ?></span>
                            </span>
                        <?php if (isset($component)) { $__componentOriginal78514cbb70010bd0b40447a7eab72b3c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal78514cbb70010bd0b40447a7eab72b3c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.bool-input','data' => ['wire:key' => ''.e($target).'-'.e($key).'','key' => ''.e($key).'','model' => $programmableSettings,'fallback' => $setting['fallback'] ?? null,'readonly' => !$setting['is_writeable']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('settings.bool-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => ''.e($target).'-'.e($key).'','key' => ''.e($key).'','model' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($programmableSettings),'fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setting['fallback'] ?? null),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$setting['is_writeable'])]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal78514cbb70010bd0b40447a7eab72b3c)): ?>
<?php $attributes = $__attributesOriginal78514cbb70010bd0b40447a7eab72b3c; ?>
<?php unset($__attributesOriginal78514cbb70010bd0b40447a7eab72b3c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal78514cbb70010bd0b40447a7eab72b3c)): ?>
<?php $component = $__componentOriginal78514cbb70010bd0b40447a7eab72b3c; ?>
<?php unset($__componentOriginal78514cbb70010bd0b40447a7eab72b3c); ?>
<?php endif; ?>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal295ad6c56f7e489bae208d7528aad833)): ?>
<?php $attributes = $__attributesOriginal295ad6c56f7e489bae208d7528aad833; ?>
<?php unset($__attributesOriginal295ad6c56f7e489bae208d7528aad833); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal295ad6c56f7e489bae208d7528aad833)): ?>
<?php $component = $__componentOriginal295ad6c56f7e489bae208d7528aad833; ?>
<?php unset($__componentOriginal295ad6c56f7e489bae208d7528aad833); ?>
<?php endif; ?>
                <?php else: ?>
                    <?php if (isset($component)) { $__componentOriginal4f4ce3621d459bb36ba5c988ba12f983 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4f4ce3621d459bb36ba5c988ba12f983 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.settings.text-input','data' => ['wire:key' => ''.e($target).'-'.e($key).'','for' => ''.e($key).'','fallback' => $setting['fallback'] ?? null,'readonly' => !$setting['is_writeable'],'settingId' => $key,'valueModel' => $model]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('settings.text-input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['wire:key' => ''.e($target).'-'.e($key).'','for' => ''.e($key).'','fallback' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($setting['fallback'] ?? null),'readonly' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(!$setting['is_writeable']),'settingId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($key),'valueModel' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($model)]); ?><?php echo e($setting['translation']); ?>

                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4f4ce3621d459bb36ba5c988ba12f983)): ?>
<?php $attributes = $__attributesOriginal4f4ce3621d459bb36ba5c988ba12f983; ?>
<?php unset($__attributesOriginal4f4ce3621d459bb36ba5c988ba12f983); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4f4ce3621d459bb36ba5c988ba12f983)): ?>
<?php $component = $__componentOriginal4f4ce3621d459bb36ba5c988ba12f983; ?>
<?php unset($__componentOriginal4f4ce3621d459bb36ba5c988ba12f983); ?>
<?php endif; ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

        </div>
    </form>
<?php else: ?>
    <p class="text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400"><?php echo app('translator')->get('Protocol does not provide programmable settings or you do not have sufficient role for reading them.'); ?></p>
<?php endif; ?><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/settings/programmable-settings.blade.php ENDPATH**/ ?>