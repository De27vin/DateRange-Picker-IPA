
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps(['advancedSettings', 'model', 'updateMethodName']) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps(['advancedSettings', 'model', 'updateMethodName']); ?>
<?php foreach (array_filter((['advancedSettings', 'model', 'updateMethodName']), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php uasort($advancedSettings, fn ($a, $b) => strcmp(strtolower($a['key']), strtolower($b['key']))); ?>

<?php if(count($advancedSettings)): ?>
    <form wire:submit.prevent.stop="<?php echo e($updateMethodName); ?>">

        <div class="mb-4 font-light">

            <table class="w-full table-auto">
                <thead>
                    <tr>
                        <th class="border px-4 py-2"><?php echo e(__('Setting')); ?></th>
                        <th class="border px-4 py-2"><?php echo e(__('Fallback level')); ?></th>
                        <th class="border px-4 py-2"><?php echo e(__('Fallback value')); ?></th>
                        <th class="border px-4 py-2"><?php echo e(__('Type')); ?></th>
                        <th class="border px-4 py-2"><?php echo e(__('Value')); ?></th>

                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $advancedSettings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $setting): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="border px-4 py-2" style="font-size: 1rem;"><?php echo e($setting['key']); ?></td>
                            <td class="border px-4 py-2" style="font-size: 1rem;"><?php echo e(str_replace(__('Fallback:').' ', '', $setting['fallback']['label'])); ?></td>
                            <td class="border px-4 py-2" style="font-size: 1rem;"><?php echo e($setting['fallback']['value']); ?></td>
                            <td class="border px-4 py-2" style="font-size: 1rem;"><?php echo e($setting['type']); ?></td>
                            <td class="border px-4 py-2" style="font-size: 1rem;">
                                <input <?php echo e((empty($setting['is_writeable']) ? ' readonly=readonly ' : '')); ?> type="text" wire:model.defer="<?php echo e($model); ?>.<?php echo e($id); ?>.value" class="w-full" style="font-size: 1rem;">
                            </td>

                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

        </div>
    </form>
<?php else: ?>
    <p class="text-sm py-2 px-4 mb-8 mx-1 text-white bg-color-new-400"><?php echo app('translator')->get('Protocol does not provide advanced settings or you do not have sufficient role for reading them.'); ?></p>
<?php endif; ?><?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\resources\views/components/settings/advanced-settings.blade.php ENDPATH**/ ?>