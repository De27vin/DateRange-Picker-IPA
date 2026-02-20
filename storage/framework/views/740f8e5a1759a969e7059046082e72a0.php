<?php $__env->startComponent($view, $params); ?>
    <?php $__env->slot($slotOrSection); ?>
        <?php echo $manager->initialDehydrate()->toInitialResponse()->effects['html']; ?>

    <?php $__env->endSlot(); ?>
<?php echo $__env->renderComponent(); ?>
<?php /**PATH C:\Users\devin\Documents\SynologyDrive\Privat\Serv24\Laravel-VueJS\Kopie-UCP-web-UI\src\vendor\livewire\livewire\src/Macros/livewire-view-component.blade.php ENDPATH**/ ?>