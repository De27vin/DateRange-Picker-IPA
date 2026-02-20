

<?php $__env->startSection('content'); ?>

    <div class="mx-auto w-full px-5 justify-start items-start">
        <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('dashboard.stats', [])->html();
} elseif ($_instance->childHasBeenRendered('SyjAGII')) {
    $componentId = $_instance->getRenderedChildComponentId('SyjAGII');
    $componentTag = $_instance->getRenderedChildComponentTagName('SyjAGII');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('SyjAGII');
} else {
    $response = \Livewire\Livewire::mount('dashboard.stats', []);
    $html = $response->html();
    $_instance->logRenderedChild('SyjAGII', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:dashboard.stats>


        <div id="vue-dashboard-filters">
            <vue-dashboard-filters></vue-dashboard-filters>
        </div>
        <script src="<?php echo e(mix('/vue/vue-dashboard-filters.js')); ?>"></script>

        <!-- Export Components (conditionally loaded) -->
        <div x-data="{ showExport: false, showExportComments: false }" 
             x-on:toggle-export.window="showExport = true"
             x-on:toggle-export-comments.window="showExportComments = true"
             x-on:dropdown-select.window="if ($event.detail.element === '') { showExport = false; showExportComments = false }">

            <div x-cloak x-show="showExport" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('ucp.export-devices-new', ['filtersId' => 'Dashboard','exportSites' => false])->html();
} elseif ($_instance->childHasBeenRendered('export-devices-component')) {
    $componentId = $_instance->getRenderedChildComponentId('export-devices-component');
    $componentTag = $_instance->getRenderedChildComponentTagName('export-devices-component');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('export-devices-component');
} else {
    $response = \Livewire\Livewire::mount('ucp.export-devices-new', ['filtersId' => 'Dashboard','exportSites' => false]);
    $html = $response->html();
    $_instance->logRenderedChild('export-devices-component', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:ucp.export-devices-new>
            </div>

            <div x-cloak x-show="showExportComments" style="display:none" class="relative bg-white bg-opacity-20 w-full p-8 pt-4 my-4">
                <?php
if (! isset($_instance)) {
    $html = \Livewire\Livewire::mount('ucp.export-comments-new', ['filtersId' => 'Dashboard','exportSites' => false])->html();
} elseif ($_instance->childHasBeenRendered('export-comments-component')) {
    $componentId = $_instance->getRenderedChildComponentId('export-comments-component');
    $componentTag = $_instance->getRenderedChildComponentTagName('export-comments-component');
    $html = \Livewire\Livewire::dummyMount($componentId, $componentTag);
    $_instance->preserveRenderedChild('export-comments-component');
} else {
    $response = \Livewire\Livewire::mount('ucp.export-comments-new', ['filtersId' => 'Dashboard','exportSites' => false]);
    $html = $response->html();
    $_instance->logRenderedChild('export-comments-component', $response->id(), \Livewire\Livewire::getRootElementTagName($html));
}
echo $html;
?></livewire:ucp.export-comments-new>
            </div>
        </div>

        <div id="vue-dashboard-list">
            <vue-dashboard-list></vue-dashboard-list>
        </div>
        <script src="<?php echo e(mix('/vue/vue-dashboard-list.js')); ?>"></script>
    </div>

<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/pages/dashboard-new.blade.php ENDPATH**/ ?>