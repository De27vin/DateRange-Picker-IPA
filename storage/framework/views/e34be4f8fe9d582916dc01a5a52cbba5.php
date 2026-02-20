

<?php $__env->startSection('content'); ?>

    <?php if (isset($component)) { $__componentOriginal70db51506c4fa960aaf2c51f5b092ae4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.new-popup','data' => ['popupId' => 'deviceSettingsCustomFields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.new-popup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['popupId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('deviceSettingsCustomFields')]); ?>
        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('ucp.device-settings-and-custom-fields', ['deviceId' => 'none'])->html();
} elseif ($_instance->childHasBeenRendered(''.e(rand()).'')) {
    $componentId = $_instance->getRenderedChildComponentId(''.e(rand()).'');
    $componentTag = $_instance->getRenderedChildComponentTagName(''.e(rand()).'');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild(''.e(rand()).'');
} else {
    $response = \Livewire\Livewire::mount('ucp.device-settings-and-custom-fields', ['deviceId' => 'none']);
    $html = $response->html();
    $_instance->logRenderedChild(''.e(rand()).'', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4)): ?>
<?php $attributes = $__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4; ?>
<?php unset($__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70db51506c4fa960aaf2c51f5b092ae4)): ?>
<?php $component = $__componentOriginal70db51506c4fa960aaf2c51f5b092ae4; ?>
<?php unset($__componentOriginal70db51506c4fa960aaf2c51f5b092ae4); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal70db51506c4fa960aaf2c51f5b092ae4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.new-popup','data' => ['popupId' => 'siteSettingsCustomFields']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.new-popup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['popupId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('siteSettingsCustomFields')]); ?>
        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('ucp.site-settings-and-custom-fields', ['deviceSiteId' => 'none'])->html();
} elseif ($_instance->childHasBeenRendered(''.e(rand()).'')) {
    $componentId = $_instance->getRenderedChildComponentId(''.e(rand()).'');
    $componentTag = $_instance->getRenderedChildComponentTagName(''.e(rand()).'');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild(''.e(rand()).'');
} else {
    $response = \Livewire\Livewire::mount('ucp.site-settings-and-custom-fields', ['deviceSiteId' => 'none']);
    $html = $response->html();
    $_instance->logRenderedChild(''.e(rand()).'', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4)): ?>
<?php $attributes = $__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4; ?>
<?php unset($__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70db51506c4fa960aaf2c51f5b092ae4)): ?>
<?php $component = $__componentOriginal70db51506c4fa960aaf2c51f5b092ae4; ?>
<?php unset($__componentOriginal70db51506c4fa960aaf2c51f5b092ae4); ?>
<?php endif; ?>

    <?php if (isset($component)) { $__componentOriginal70db51506c4fa960aaf2c51f5b092ae4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal.new-popup','data' => ['popupId' => 'cliConfirmationModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal.new-popup'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['popupId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('cliConfirmationModal')]); ?>
        <?php if (isset($component)) { $__componentOriginala2a85c9a4729da0a7e1acbdc5dc21473 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2a85c9a4729da0a7e1acbdc5dc21473 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.modal-content.cli-confirmation-for-new-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('modal-content.cli-confirmation-for-new-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?> <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2a85c9a4729da0a7e1acbdc5dc21473)): ?>
<?php $attributes = $__attributesOriginala2a85c9a4729da0a7e1acbdc5dc21473; ?>
<?php unset($__attributesOriginala2a85c9a4729da0a7e1acbdc5dc21473); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2a85c9a4729da0a7e1acbdc5dc21473)): ?>
<?php $component = $__componentOriginala2a85c9a4729da0a7e1acbdc5dc21473; ?>
<?php unset($__componentOriginala2a85c9a4729da0a7e1acbdc5dc21473); ?>
<?php endif; ?>
     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4)): ?>
<?php $attributes = $__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4; ?>
<?php unset($__attributesOriginal70db51506c4fa960aaf2c51f5b092ae4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal70db51506c4fa960aaf2c51f5b092ae4)): ?>
<?php $component = $__componentOriginal70db51506c4fa960aaf2c51f5b092ae4; ?>
<?php unset($__componentOriginal70db51506c4fa960aaf2c51f5b092ae4); ?>
<?php endif; ?>

    <div class="mx-auto w-full px-5 justify-start items-start">


        <!-- NEW VUE FILTERS (experimental) -->
        <div id="vue-equipment-filters">
            <vue-equipment-filters></vue-equipment-filters>
        </div>
        <script src="<?php echo e(mix('/vue/vue-equipment-filters.js')); ?>"></script>

        <!-- Export Components (conditionally loaded) -->
        <div x-data="{ showExport: false, showExportComments: false }"
             x-on:toggle-export.window="showExport = true"
             x-on:toggle-export-comments.window="showExportComments = true"
             x-on:dropdown-select.window="if ($event.detail.element === '') { showExport = false; showExportComments = false }">

            <div x-cloak x-show="showExport" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('ucp.export-devices-new', ['filtersId' => 'Equipment','exportSites' => true])->html();
} elseif ($_instance->childHasBeenRendered('export-devices-component')) {
    $componentId = $_instance->getRenderedChildComponentId('export-devices-component');
    $componentTag = $_instance->getRenderedChildComponentTagName('export-devices-component');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('export-devices-component');
} else {
    $response = \Livewire\Livewire::mount('ucp.export-devices-new', ['filtersId' => 'Equipment','exportSites' => true]);
    $html = $response->html();
    $_instance->logRenderedChild('export-devices-component', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:ucp.export-devices-new>
            </div>

            <div x-cloak x-show="showExportComments" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('ucp.export-comments-new', ['filtersId' => 'Equipment','exportSites' => true])->html();
} elseif ($_instance->childHasBeenRendered('export-comments-component')) {
    $componentId = $_instance->getRenderedChildComponentId('export-comments-component');
    $componentTag = $_instance->getRenderedChildComponentTagName('export-comments-component');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('export-comments-component');
} else {
    $response = \Livewire\Livewire::mount('ucp.export-comments-new', ['filtersId' => 'Equipment','exportSites' => true]);
    $html = $response->html();
    $_instance->logRenderedChild('export-comments-component', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:ucp.export-comments-new>
            </div>
        </div>

        <div id="vue-equipment-list" class="px-2">
            <vue-equipment-list></vue-equipment-list>
        </div>
        <script src="<?php echo e(mix('/vue/vue-equipment-list.js')); ?>"></script>
    </div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/pages/equipment-new.blade.php ENDPATH**/ ?>