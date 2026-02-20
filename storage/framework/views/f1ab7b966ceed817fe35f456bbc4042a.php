<?php if($name): ?>
    <template x-for="value in sortOrder" x-key="index">
        <input
            type="hidden"
            name="<?php echo e($name); ?>[]"
            x-model:value="value"
        />
    </template>
<?php endif; ?>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\vendor\jacekdziurdzikowski\laravel-blade-sortable\src/../resources/views/includes/hidden-inputs.blade.php ENDPATH**/ ?>